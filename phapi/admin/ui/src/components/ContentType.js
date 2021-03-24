import React, { useContext, useMemo } from "react";
import { Route, Switch, useParams } from "react-router";
import { SchemaContext } from "../App";
import EditEntry from "./EditEntry";
import ListEntries from "./ListEntries";

export default function ContentType({ match }) {
  const { model } = useParams();
  const getSchema = useContext(SchemaContext);
  const schema = useMemo(() => getSchema(model), [getSchema, model]);

  return (
    schema && (
      <Switch>
        <Route path={`${match.path}/create`}>
          <EditEntry isCreating={true} schema={schema} />
        </Route>
        <Route path={`${match.path}/:id`}>
          <EditEntry isCreating={false} schema={schema} />
        </Route>
        <Route path={match.path} exact>
          <ListEntries schema={schema} />
        </Route>
      </Switch>
    )
  );
}
