import {
  Alert,
  AlertIcon,
  Center,
  ChakraProvider,
  Spinner,
} from "@chakra-ui/react";
import React, { useContext, useEffect } from "react";
import {
  BrowserRouter as Router,
  Redirect,
  Route,
  Switch,
} from "react-router-dom";
import "./App.css";
import { AuthBackend, AuthConext } from "./auth/auth";
import ContentType from "./components/ContentType";
import Layout from "./Layout";
import Login from "./pages/Login";
import useRequest from "./utils/useRequest";
import useRequestRunner from "./utils/useRequestRunner";

export const SchemaContext = React.createContext();

function SchemaBackend({ children }) {
  const schemaApi = useRequest(
    `http://192.168.0.10/~bendeguz/phapi/admin/content-schema/models`
  );
  const schemaHandler = useRequestRunner(schemaApi.list);
  const auth = useContext(AuthConext);
  useEffect(schemaHandler.run, [auth.user]);

  return (
    <>
      {schemaHandler.error && (
        <Alert status="error">
          <AlertIcon />
          {schemaHandler.error}
        </Alert>
      )}
      {schemaHandler.isLoading && (
        <Center w="100vw" h="100vh">
          <Spinner></Spinner>
        </Center>
      )}
      {schemaHandler.isSuccess && (
        <SchemaContext.Provider
          value={(key) =>
            schemaHandler.result &&
            schemaHandler.result.find((x) => x.key === key)
          }
        >
          {children}
        </SchemaContext.Provider>
      )}
    </>
  );
}

function App() {
  return (
    <ChakraProvider>
      <Router>
        <AuthBackend>
          <AuthConext.Consumer>
            {(auth) => !auth.user && <Redirect to="/login" />}
          </AuthConext.Consumer>
          <SchemaBackend>
            <Layout>
              <Switch>
                <Route path="/login" component={Login} />
                <Route path="/content-type/:model" component={ContentType} />
              </Switch>
            </Layout>
          </SchemaBackend>
        </AuthBackend>
      </Router>
    </ChakraProvider>
  );
}

export default App;
