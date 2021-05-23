<?php

namespace Phroper\Services;

use Phroper\Phroper;
use Phroper\Service;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Throwable;

class Email extends Service {
    protected function getTemplatePath($template) {
        $template = str_kebab_pc($template);
        if (file_exists(Phroper::dir("EmailTemplates", $template . ".php")))
            return Phroper::dir("EmailTemplates", $template . ".php");
        if (Phroper::getCachedType("EmailTemplates\\" . $template))
            return Phroper::getCachedType("EmailTemplates\\" . $template);
        return false;
    }

    public function processTemplate($template, $data = []) {
        $path = $this->getTemplatePath($template);
        if (!$path) {
            throw new Exception("Email template not found: " . $template);
        }

        // Save old template data
        $old_td = $this->templateData;
        $this->templateData = $data;

        ob_start();
        require($path);
        $body = ob_get_contents();
        ob_end_clean();

        // Restore template data
        $this->templateData = $old_td;

        return $body;
    }

    public function send($template, $address, $data = []) {
        Phroper::addBackgroundTask(fn () => $this->sendSync($template, $address, $data));
    }

    public function sendSync($template, $address, $data = []) {
        try {
            // Initialize mailer
            $mail = Phroper::ini("MAIL");
            if (is_callable($mail)) $mail = $mail();

            $mail->addAddress($address);

            // Process body
            $this->currentMail = $mail;
            $body = $this->processTemplate($template, $data);
            $this->currentMail = null;
            $mail->Body = $body;

            // Send mail
            $mail->send();
        } catch (Throwable $e) {
            Phroper::service("log")->error(
                "Email sending error (" . $template . ", " . $address . ")\n" . $e
            );
            throw $e;
        }
        Phroper::service("log")->info(
            "Email sent (" . $template . ", " . $address . ")"
        );

        return true;
    }

    public function allowDefaultController() {
        return false;
    }

    private array $templateData = [];
    public function getTemplateData() {
        return $this->templateData;
    }

    private $currentMail = null;
    public function getCurrentMail() {
        return $this->currentMail;
    }
}
