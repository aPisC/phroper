import { Box, Button, HStack, Stack, Text } from "@chakra-ui/react";
import { Form, Formik } from "formik";
import { default as React, useEffect, useMemo } from "react";
import { useParams } from "react-router";
import { useHistory } from "react-router-dom";
import useRequest from "../../utils/useRequest";
import useRequestRunner from "./../../utils/useRequestRunner";
import EditorForm from "./EditorForm";
import EditorInfo from "./EditorInfo";

export default function EntryEditor({ isCreating, schema }) {
  const { model, id } = useParams();
  const history = useHistory();
  const contentApi = useRequest(`/admin/content-manager/${model}`);

  console.log(schema);

  const contentHandler = useRequestRunner(() => contentApi.get(id), []);
  useEffect(() => {
    if (!isCreating) contentHandler.run();
  }, []);

  const editorContext = {
    isCreating,
    history,
    schema,
    id,
    contentHandler,
    contentApi,
  };

  return (
    <>
      <Box>
        <Text fontSize={40} mb={4}>
          {contentHandler.result
            ? `${contentHandler.result[schema.display]} (${schema.name})`
            : schema.name}
        </Text>
        <FormikWrapper {...editorContext}>
          <HStack mb={6}>
            <Button
              colorScheme="red"
              onClick={() => history.goBack()}
              variant="link"
            >
              Back
            </Button>
          </HStack>
          <Stack
            flex={1}
            direction={{ sm: "column", xl: "row" }}
            alignItems={{ sm: "stretch", xl: "flex-start" }}
          >
            <EditorForm {...editorContext} />
            <EditorInfo {...editorContext} />
          </Stack>
        </FormikWrapper>
      </Box>
    </>
  );
}

function FormikWrapper({
  children,
  isCreating,
  schema,
  id,
  contentHandler,
  contentApi,
}) {
  const history = useHistory();

  const formikInitialValues = useMemo(() => {
    const initVals = {};
    Object.values(schema.fields).forEach((field) => {
      if (field.default != null && !field.auto)
        initVals[field.key] = field.default;
    });
    return initVals;
  }, [schema]);
  return (
    (isCreating || contentHandler.isSuccess) && (
      <Formik
        initialValues={isCreating ? formikInitialValues : contentHandler.result}
        onSubmit={async (data) => {
          const result = await contentHandler.run(
            isCreating ? contentApi.create(data) : contentApi.update(data, id)
          );
          if (!result) return;
          if (isCreating) {
            history.replace("./" + result[schema.primary]);
          }
        }}
      >
        <Form>{children}</Form>
      </Formik>
    )
  );
}
