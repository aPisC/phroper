import { Select, Skeleton } from "@chakra-ui/react";
import React, { useContext, useEffect, useMemo } from "react";
import useRequest from "../../../utils/useRequest";
import { SchemaContext } from "./../../../App";
import useRequestRunner from "./../../../utils/useRequestRunner";

export default function RelationOne({ schema, placeholder, value, ...props }) {
  const getSchema = useContext(SchemaContext);
  const modelSchema = useMemo(() => getSchema(schema.model), [
    getSchema,
    schema,
  ]);
  const contentHandler = useRequestRunner(
    useRequest(
      `/admin/content-manager/${schema.model}${
        value ? `?${modelSchema.primary}_sort=${value}` : ""
      }`
    ).list
  );
  useEffect(contentHandler.run, []);

  const entities = contentHandler.result;
  const optionList = useMemo(
    () =>
      modelSchema &&
      entities &&
      entities.map((e) => (
        <option value={e[modelSchema.primary]}>
          {" "}
          {e[modelSchema.display]}
        </option>
      )),
    [modelSchema, entities]
  );

  if (contentHandler.isLoading) return <Skeleton h={8} />;

  return (
    <Select value={value} {...props}>
      {optionList}
    </Select>
  );
}
