import * as yup from "yup";

export default function GenerateYup(schema) {
  if (!schema) return null;

  const result = {};

  Object.keys(schema.fields).forEach((key) => {
    const field = schema.fields[key];
    let fieldSchema = null;

    switch (field.type) {
      case "file_multi":
        fieldSchema = yup.array(
          yup.lazy((value) => {
            switch (typeof value) {
              case "object":
                return yup.object({ id: yup.number().required() });
              default:
                return yup.number().required();
            }
          })
        );
        break;
      default:
        break;
    }

    if (fieldSchema) result[key] = fieldSchema;
  });
  return yup.object(result);
}
