import { ChakraProvider } from "@chakra-ui/react";
import { BrowserRouter as Router, Route, Switch } from "react-router-dom";
import "./App.css";
import { AuthBackend } from "./auth/auth";
import ContentType from "./components/ContentType";
import Layout from "./Layout";
import Login from "./pages/Login";

function App() {
  return (
    <ChakraProvider>
      <Router>
        <AuthBackend>
          <Layout>
            <Switch>
              <Route path="/login" component={Login} />
              <Route path="/content-type/:model" component={ContentType} />
            </Switch>
          </Layout>
        </AuthBackend>
      </Router>
    </ChakraProvider>
  );
}

export default App;
