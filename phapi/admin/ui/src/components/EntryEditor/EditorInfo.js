import { Box, FormControl, FormLabel, Text } from "@chakra-ui/react";
import React from "react";
import { FieldComponentMap } from "./Fields";

export default function EditorInfo({ schema }) {
  return (
    <Box minW="350px" p={4} bg="white">
      <Text fontWeight="bold" fontSize={20}>
        Information
      </Text>
      {Object.keys(schema.fields).map((f) => (
        <InfoField schema={schema.fields[f]} key={f} />
      ))}
    </Box>
  );
}

function InfoField({ schema }) {
  const EditComponent = FieldComponentMap.display_info;
  if (schema.auto && schema.readonly)
    return (
      EditComponent && (
        <FormControl>
          <FormLabel>{schema.name}</FormLabel>
          <EditComponent
            as={Text}
            name={schema.key}
            disabled={true}
            schema={schema}
            color="gray.800"
            fontSize="14"
            ml={4}
          />
        </FormControl>
      )
    );
  return null;
}
