import {
  Alert,
  AlertDescription,
  AlertIcon,
  Box,
  Button,
  Container,
  FormControl,
  FormLabel,
  Input,
  Stack,
  Text,
} from "@chakra-ui/react";
import { ErrorMessage, Field, Form, Formik, FormikConsumer } from "formik";
import React, { useContext } from "react";
import { useHistory } from "react-router";
import * as yup from "yup";
import { AuthConext } from "../auth/auth";

export default function Login() {
  return (
    <Container minH={10} p={0} bgColor="gray.200" mt="10vh">
      <Text fontSize={32} p={2} bg="red.500" color="white" mb={4}>
        Login
      </Text>
      <Box p={4}>
        <LoginForm />
      </Box>
    </Container>
  );
}

function LoginForm() {
  const auth = useContext(AuthConext);
  const history = useHistory();

  return (
    <Formik
      validationSchema={yup.object().shape({
        username: yup.string().required("Username is required"),
        password: yup.string().required("Password is required"),
      })}
      initialValues={{
        username: "",
        password: "",
      }}
      onSubmit={async (props, { setErrors }) => {
        try {
          await auth.login(props.username, props.password);

          let state = history.location.state;
          while (
            state &&
            state.redirect &&
            state.redirect.pathname === "/login"
          )
            state = state.redirect.state;

          if (state && state.redirect)
            history.push(state.redirect.pathname, state.redirect.state);
          else history.push("/");
        } catch (err) {
          setErrors({ password: err.message });
        }
      }}
    >
      <Form>
        <Stack spacing={4}>
          {history.location.state && history.location.state.error && (
            <Alert status="error">
              <AlertIcon />
              <AlertDescription>
                {history.location.state.error}
              </AlertDescription>
            </Alert>
          )}

          <FormControl>
            <FormLabel>Username</FormLabel>
            <Field as={Input} bgColor="white" type="text" name="username" />
            <ErrorMessage name="username" component={Text} color="red" />
          </FormControl>
          <FormControl>
            <FormLabel>Password</FormLabel>
            <Field as={Input} bgColor="white" type="password" name="password" />
            <ErrorMessage name="password" component={Text} color="red" />
          </FormControl>

          <Box textAlign="right" pt={2}>
            <FormikConsumer>
              {({ isSubmitting }) => (
                <Button
                  isLoading={isSubmitting}
                  type="submit"
                  variant="solid"
                  colorScheme="red"
                  children="Log in"
                />
              )}
            </FormikConsumer>
          </Box>
        </Stack>
      </Form>
    </Formik>
  );
}
