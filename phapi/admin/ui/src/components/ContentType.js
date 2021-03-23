import React, { useEffect } from "react";
import useRequest from "../utils/useRequest";
import useRequestRunner from "../utils/useRequestRunner";

export default function ContentType({ modelName }) {
  const schemaApi = useRequest(
    `http://192.168.0.10/~bendeguz/phapi/admin/content-schema/model/${modelName}`
  );
  const schemaHandler = useRequestRunner(schemaApi.list, null);
  useEffect(schemaHandler.run, []);

  console.log(schemaHandler.result);
  return <div></div>;
}
