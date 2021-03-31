import { ArrowUpIcon } from "@chakra-ui/icons";
import { AspectRatio, Box, Center, Image, Spinner } from "@chakra-ui/react";
import { ErrorMessage } from "formik";
import React, { useCallback, useEffect, useState } from "react";
import useRequest from "../../../utils/useRequest";
import useRequestRunner from "../../../utils/useRequestRunner";

export default function FileOne({ name, value, onChange }) {
  const [dragOver, setDragOver] = useState(false);

  const uploadsApi = useRequest("/api/file-upload");
  const uploadHandler = useRequestRunner();

  useEffect(() => {
    const v = value && typeof value == "object" ? value.id : value;
    if (v) uploadHandler.run(uploadsApi.get(v));
  }, [value]);

  const handleDrag = useCallback(
    async (event) => {
      console.log(event);
      event.preventDefault();
      setDragOver(false);

      if (
        event.dataTransfer.items &&
        event.dataTransfer.items.length === 1 &&
        event.dataTransfer.items[0].kind === "file"
      ) {
        const formData = new FormData();
        formData.append("file", event.dataTransfer.files[0]);

        const result = await uploadHandler.run(
          uploadsApi.send("upload", formData, "POST", {
            "Content-Type": "multipart/form-data",
          })
        );
        if (onChange) {
          onChange({ target: { value: result.id, name: name } });
        }
      }
    },
    [setDragOver]
  );

  return (
    <AspectRatio
      bg={"gray.800"}
      borderRadius={4}
      overflow="hidden"
      borderColor="gray.500"
      ratio={16 / 9}
    >
      <Box>
        {uploadHandler.result && uploadHandler.result.filename && (
          <Center position="absolute" h="100%" w="100%">
            <Image src={uploadHandler.result.filename} />
          </Center>
        )}
        <ErrorMessage name={name}>
          {(msg) => (
            <Box
              w="100%"
              h="100%"
              bg="red.800"
              color="white"
              p={4}
              children={msg}
              position="absolute"
            />
          )}
        </ErrorMessage>
        {uploadHandler.isLoading && (
          <Center position="absolute" h="100%" w="100%">
            <Spinner size="lg" color="white" />
          </Center>
        )}
        {dragOver && (
          <Center
            position="absolute"
            h="100%"
            w="100%"
            bg={dragOver ? "#88888866" : null}
          >
            <ArrowUpIcon color="white" fontSize={100} />
          </Center>
        )}
        <Box
          w="100%"
          h="100%"
          onDragOver={(event) => {
            event.preventDefault();
            setDragOver(true);
          }}
          onDragLeave={() => setDragOver(false)}
          onDrop={handleDrag}
          position="absolute"
          zIndex={1000}
        />
      </Box>
    </AspectRatio>
  );
}
