import axios from "axios";
import { useContext, useMemo } from "react";
import { AuthConext } from "../auth/auth";

export default function useRequest(apiUrl, jwt = null) {
  const auth = useContext(AuthConext);

  const handler = useMemo(() => {
    const send = async (url, body, method, headers) => {
      try {
        const response = await axios({
          method,
          url,
          data: body,
          headers:
            jwt || auth?.jwt
              ? { Authorization: "Bearer " + (jwt || auth?.jwt), ...headers }
              : { ...headers },
        });
        return response.data;
      } catch (err) {
        if (err.code === "401") {
          auth.logout();
        }
        if (
          err.response &&
          err.response.data &&
          typeof err.response.data === "object"
        ) {
          throw new Error(err.response.data.message);
        }
        throw new Error("Server is not available.");
      }
    };

    return {
      list: async (page = null) =>
        send(apiUrl + (page ? `?page=${page}` : ""), null, "GET"),
      get: async (id) => send(apiUrl + "/" + id, null, "GET"),
      create: async (data) => send(apiUrl, data, "POST"),
      update: async (data, id = null) =>
        send(apiUrl + "/" + (id == null ? data.id : id), data, "PUT"),
      delete: async (id) =>
        send(
          apiUrl + "/" + (typeof id === "object" ? id.id : id),
          null,
          "DELETE"
        ),
      send: async (url, data, method = "GET", headers = {}) =>
        send(apiUrl + "/" + url, data, method, headers),
    };
  }, [apiUrl, auth, jwt]);

  return handler;
}
