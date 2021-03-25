import { Input, Select, Switch, Text } from "@chakra-ui/react";
import { connect } from "formik";
import RelationOne from "./RelationOne";

export const FieldComponentMap = {
  default: Input,
  default_: connect(({ name, formik }) => (
    <Input disabled value={formik.values[name]} />
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
};
