import { Button } from "@chakra-ui/button";
import React, { useState } from "react";

export default function AsyncButton({ onClick, ...props }) {
  const [isLoading, setIsLoading] = useState(false);
  const onClick2 = async (...p) => {
    setIsLoading(true);
    try {
      await onClick(...p);
    } catch (e) {}
    setIsLoading(false);
  };
  return <Button {...props} onClick={onClick2} isLoading={isLoading} />;
}
