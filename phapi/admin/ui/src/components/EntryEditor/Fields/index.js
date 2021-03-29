import { Input, Select, Switch, Text } from "@chakra-ui/react";
import { connect } from "formik";
import FileOne from "./FileOne";
import RelationOne from "./RelationOne";

export const FieldComponentMap = {
  default: Input,
  password: connect(({ name, formik, ...props }) => (
    <Input type="password" name={name} value={formik.values[name]} {...props} />
  )),
  bool: connect(({ formik, name, ...props }) => (
    <Switch
      size="lg"
      colorScheme="red"
      {...props}
      isChecked={formik.values && formik.values[name]}
      onChange={() => formik.setFieldValue(name, !formik.values[name])}
    />
  )),
  enum: ({ schema, placeholder, ...props }) => (
    <Select {...props}>
      {schema.values.map((v) => (
        <option value={v}>{v}</option>
      ))}
    </Select>
  ),
  relation_one: RelationOne,
  relation_many: false,
  email: (props) => <Input type="email" {...props} />,
  display_info: connect(({ name, formik, ...props }) => (
    <Text {...props}>{formik.values && formik.values[name]}</Text>
  )),
  file: FileOne,
};
