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
  Tab,
  TabList,
  TabPanel,
  TabPanels,
  Tabs,
  Text,
} from "@chakra-ui/react";
import { ErrorMessage, Field, Form, Formik, FormikConsumer } from "formik";
import React, { useContext } from "react";
import { useHistory } from "react-router";
import * as yup from "yup";
import { AuthConext } from "../auth/auth";

export default function Login() {
  return (
    <Container px={0} minH={10} bgColor="gray.200" mt="10vh">
      <Tabs isFitted isLazy colorScheme="green">
        <TabList>
          <Tab>Login </Tab>
          <Tab>Register</Tab>
        </TabList>
        <TabPanels p={4}>
          <TabPanel>
            <LoginForm />
          </TabPanel>
          <TabPanel>
            <RegisterForm />
          </TabPanel>
        </TabPanels>
      </Tabs>
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
                  colorScheme="green"
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

function RegisterForm() {
  const auth = useContext(AuthConext);
  const history = useHistory();
  return (
    <Formik
      validationSchema={yup.object().shape({
        name: yup.string().required("Name is required"),
        email: yup
          .string()
          .required("Email is required")
          .email("Wrong email format"),
        username: yup.string().required("Username is required"),
        password: yup
          .string()
          .required("Password is required")
          .min(8, "Password must be at least 8 characters")
          .matches(
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])/,
            "Password must contain at least one uppercase, one lowercase letter and one number"
          ),
        password2: yup
          .string()
          .required("Passwords must match")
          .oneOf([yup.ref("password"), null], "Passwords must match"),
      })}
      initialValues={{
        username: "",
        password: "",
        password2: "",
        name: "",
        email: "",
      }}
      onSubmit={async (props, { setErrors }) => {
        try {
          await auth.register(props);
          if (history.location.state && history.location.state.redirect)
            history.push(
              history.location.state.redirect.pathname,
              history.location.state.redirect.state
            );
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
            <FormLabel>Email</FormLabel>
            <Field as={Input} bgColor="white" type="email" name="email" />
            <ErrorMessage name="email" component={Text} color="red" />
          </FormControl>
          <FormControl>
            <FormLabel>Full name</FormLabel>
            <Field as={Input} bgColor="white" type="text" name="name" />
            <ErrorMessage name="name" component={Text} color="red" />
          </FormControl>
          <FormControl>
            <FormLabel>Password</FormLabel>
            <Field as={Input} bgColor="white" type="password" name="password" />
            <ErrorMessage name="password" component={Text} color="red" />
          </FormControl>
          <FormControl>
            <FormLabel>Confirm Password</FormLabel>
            <Field
              as={Input}
              bgColor="white"
              type="password"
              name="password2"
            />
            <ErrorMessage name="password2" component={Text} color="red" />
          </FormControl>

          <Box textAlign="right" pt={2}>
            <FormikConsumer>
              {({ isSubmitting }) => (
                <Button
                  type="submit"
                  variant="solid"
                  colorScheme="green"
                  isLoading={isSubmitting}
                  children="Register"
                />
              )}
            </FormikConsumer>
          </Box>
        </Stack>
      </Form>
    </Formik>
  );
}
