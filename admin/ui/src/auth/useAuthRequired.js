import { useContext } from "react";
import { useHistory } from "react-router";
import { AuthConext } from "./auth";

export default function useAuthRequired() {
  const auth = useContext(AuthConext);
  const history = useHistory();

  if (!auth.user) {
    history.push("/login", {
      error: "Login required",
      redirect: history.location,
    });
  }
}
