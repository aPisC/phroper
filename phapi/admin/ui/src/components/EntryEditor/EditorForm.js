import {
  Box,
  Button,
  FormControl,
  FormLabel,
  Grid,
  HStack,
} from "@chakra-ui/react";
import { Field } from "formik";
import React from "react";
import { useHistory } from "react-router";
import { FieldComponentMap } from "./Fields";

export default function EditorForm({
  schema,
  isCreating,
  contentHandler,
  contentApi,
  id,
}) {
  const history = useHistory();

  return (
    <Box p={4} bg="white" flex={1}>
      <Grid
        templateColumns={{
          sm: "repeat(1, 1fr)",
          lg: "repeat(2, 1fr)",
          "2xl": "repeat(3, 1fr)",
        }}
        flex={1}
        gap={6}
        mb={6}
      >
        {Object.keys(schema.fields).map((fn) => (
          <SchemaField
            key={fn}
            schema={schema.fields[fn]}
            isCreating={isCreating}
          />
        ))}
      </Grid>
      <HStack w="100%" justifyContent="space-between">
        {!isCreating && (
          <Button
            type="button"
            colorScheme="red"
            onClick={async () => {
              await contentHandler.run(contentApi.delete(id));
              history.goBack();
            }}
          >
            Delete
          </Button>
        )}
        <Button type="submit" colorScheme="green">
          Save
        </Button>
      </HStack>
    </Box>
  );
}

function SchemaField({ schema, isCreating }) {
  const EditComponent =
    FieldComponentMap[schema.type] ?? FieldComponentMap.default;
  const disabled = isCreating ? schema.auto : schema.readonly;
  if (schema.auto && schema.readonly) return null;
  return (
    EditComponent && (
      <FormControl>
        <FormLabel>{schema.name}</FormLabel>
        <Field
          as={EditComponent}
          name={schema.key}
          placeholder={schema.name}
          disabled={disabled}
          schema={schema}
          required={
            !disabled && schema.required && (!schema.private || isCreating)
          }
        />
      </FormControl>
    )
  );
}
