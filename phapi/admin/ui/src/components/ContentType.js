import React, { useContext, useMemo } from "react";
import { Route, Switch, useParams } from "react-router";
import { SchemaContext } from "../App";
import EntryEditor from "./EntryEditor";
import ListEntries from "./ListEntries";

export default function ContentType({ match }) {
  const { model } = useParams();
  const getSchema = useContext(SchemaContext);
  const schema = useMemo(() => getSchema(model), [getSchema, model]);

  return (
    schema && (
      <Switch>
        <Route path={`${match.path}/create`}>
          <EntryEditor key={schema.key} isCreating={true} schema={schema} />
        </Route>
        <Route path={`${match.path}/:id`}>
          <EntryEditor key={schema.key} isCreating={false} schema={schema} />
        </Route>
        <Route path={match.path} exact>
          <ListEntries key={schema.key} schema={schema} />
        </Route>
      </Switch>
    )
  );
}
