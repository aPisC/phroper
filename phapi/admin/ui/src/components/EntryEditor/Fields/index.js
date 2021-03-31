import {
  FormControl,
  FormLabel,
  Input,
  Select,
  Switch,
  Text,
} from "@chakra-ui/react";
import { connect, Field } from "formik";
import React from "react";
import FileMulti from "./FileMulti";
import FileOne from "./FileOne";
import RelationOne from "./RelationOne";

function ConnectSchemaField(EditComponent) {
  return connect(({ schema, isCreating }) => {
    const disabled = isCreating ? schema.auto : schema.readonly;
    if (schema.auto && schema.readonly) return null;
    return (
      EditComponent && (
        <FormControl minW="20%">
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
  });
}

export const FieldComponentMap = {
  default: ConnectSchemaField(Input),
  password: ConnectSchemaField(({ name, formik, ...props }) => (
    <Input type="password" name={name} value={formik.values[name]} {...props} />
  )),
  bool: ConnectSchemaField(({ formik, name, ...props }) => (
    <Switch
      size="lg"
      colorScheme="red"
      {...props}
      isChecked={formik.values && formik.values[name]}
      onChange={() => formik.setFieldValue(name, !formik.values[name])}
    />
  )),
  enum: ConnectSchemaField(({ schema, placeholder, ...props }) => (
    <Select {...props}>
      {schema.values.map((v) => (
        <option value={v}>{v}</option>
      ))}
    </Select>
  )),
  relation_one: ConnectSchemaField(RelationOne),
  relation_many: false,
  email: ConnectSchemaField((props) => <Input type="email" {...props} />),
  display_info: ConnectSchemaField(({ name, formik, ...props }) => (
    <Text {...props}>{formik.values && formik.values[name]}</Text>
  )),
  file: ConnectSchemaField(FileOne),
  file_multi: FileMulti,
};
