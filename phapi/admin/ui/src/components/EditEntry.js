import {
  Box,
  Button,
  FormControl,
  FormLabel,
  HStack,
  Input,
  Switch,
  Text,
  VStack,
} from "@chakra-ui/react";
import { connect, Field, Form, Formik } from "formik";
import { default as React, useEffect, useMemo } from "react";
import { useParams } from "react-router";
import { useHistory } from "react-router-dom";
import useRequest from "../utils/useRequest";
import useRequestRunner from "../utils/useRequestRunner";

export default function EditEntry({ isCreating, schema }) {
  const { model, id } = useParams();
  const history = useHistory();
  const contentApi = useRequest(
    `http://192.168.0.10/~bendeguz/phapi/admin/content-manager/${model}`
  );

  const contentHandler = useRequestRunner(() => contentApi.get(id), []);
  useEffect(() => {
    if (!isCreating) contentHandler.run();
  }, []);

  const formikInitialValues = useMemo(() => {
    const initVals = {};
    Object.values(schema.fields).forEach((field) => {
      if (field.default != null && !field.auto)
        initVals[field.key] = field.default;
    });
    return initVals;
  }, [schema]);

  return (
    <>
      <Box>
        <Text fontSize={40} mb={4}>
          {contentHandler.result[schema.display]} ({schema.name})
        </Text>
        <HStack mb={6}>
          <Button
            colorScheme="red"
            aria-label="Search database"
            onClick={() => history.goBack()}
            variant="link"
          >
            Back
          </Button>
          <HStack></HStack>
        </HStack>
        <Box maxW="960px" m="auto">
          {(isCreating || contentHandler.isSuccess) && (
            <Formik
              initialValues={
                isCreating ? formikInitialValues : contentHandler.result
              }
              onSubmit={async (data) => {
                const result = await contentHandler.run(
                  isCreating
                    ? contentApi.create(data)
                    : contentApi.update(data, id)
                );
                if (!result) return;
                if (isCreating) {
                  history.replace("./" + result[schema.primary]);
                }
              }}
            >
              <Form>
                <VStack mb={4}>
                  {Object.keys(schema.fields).map((fn) => (
                    <SchemaField
                      key={fn}
                      schema={schema.fields[fn]}
                      isCreating={isCreating}
                    />
                  ))}
                </VStack>
                <HStack w="100%" justifyContent="space-between">
                  {!isCreating && (
                    <Button
                      type="button"
                      colorScheme="red"
                      onClick={async () => {
                        await contentHandler.run(contentApi.delete(id));
                        history.replace("./");
                      }}
                    >
                      Delete
                    </Button>
                  )}
                  <Button type="submit" colorScheme="green">
                    Save
                  </Button>
                </HStack>
              </Form>
            </Formik>
          )}
        </Box>
      </Box>
    </>
  );
}

const editFieldMap = {
  default: Input,
  default_: connect(({ name, formik }) => (
    <Input disabled value={formik.values[name]} />
  )),
  bool: connect(({ formik, name, ...props }) => {
    console.log(formik);
    return (
      <Switch
        isChecked={formik.values && formik.values[name]}
        onChange={() => formik.setFieldValue(name, !formik.values[name])}
      />
    );
  }),
  email: (props) => <Input type="email" {...props} />,
};

function SchemaField({ schema, isCreating }) {
  const EditComponent = editFieldMap[schema.type] || editFieldMap.default;
  return (
    <FormControl>
      <FormLabel>{schema.name}</FormLabel>
      <Field
        as={EditComponent}
        name={schema.key}
        placeholder={schema.name}
        disabled={isCreating ? schema.auto : schema.readonly}
      />
    </FormControl>
  );
}
