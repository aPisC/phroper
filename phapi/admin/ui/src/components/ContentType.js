import { Alert, AlertIcon, SkeletonText } from "@chakra-ui/react";
import React, { useEffect } from "react";
import { Route, Switch, useParams } from "react-router";
import useRequest from "../utils/useRequest";
import useRequestRunner from "../utils/useRequestRunner";
import EditEntry from "./EditEntry";
import ListEntries from "./ListEntries";

export default function ContentType({ match }) {
  const { model } = useParams();

  const schemaApi = useRequest(
    `http://192.168.0.10/~bendeguz/phapi/admin/content-schema/model/${model}`
  );
  const schemaHandler = useRequestRunner(schemaApi.list, null);
  // eslint-disable-next-line
  useEffect(schemaHandler.run, [model]);

  return (
    <div>
      {schemaHandler.error && (
        <Alert status="error">
          <AlertIcon />
          {schemaHandler.error}
        </Alert>
      )}
      {schemaHandler.isSuccess && (
        <Switch>
          <Route path={`${match.path}/create`}>
            <EditEntry isCreating={true} />
          </Route>
          <Route path={`${match.path}/:id`}>
            <EditEntry isCreating={false} />
          </Route>
          <Route path={match.path} exact>
            <ListEntries schema={schemaHandler.result} />
          </Route>
        </Switch>
      )}
      {schemaHandler.isLoading && <SkeletonText h="200" />}
    </div>
  );
}
