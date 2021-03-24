import { Box, Center, Spinner, Text, VStack } from "@chakra-ui/react";
import React, { useEffect } from "react";
import { Link } from "react-router-dom";
import useRequest from "./utils/useRequest";
import useRequestRunner from "./utils/useRequestRunner";

export default function Layout({ children }) {
  const schemaApi = useRequest(
    `http://192.168.0.10/~bendeguz/phapi/admin/content-schema/models`
  );
  const schemaHandler = useRequestRunner(schemaApi.list);
  useEffect(schemaHandler.run, []);

  return (
    <>
      {schemaHandler.isLoading && (
        <Center w="100vw" h="100vh">
          <Spinner></Spinner>
        </Center>
      )}
      {schemaHandler.isSuccess && (
        <Box minH="100%" w="100%">
          <Box
            position="fixed"
            left={0}
            px={3}
            w="250px"
            top={0}
            minH="100%"
            bg="red.600"
            color="white"
          >
            <Text fontSize={40} mb={4} w="100%" textAlign="center">
              Phapi
            </Text>

            <Text fontSize={24} mb={2}>
              Content types
            </Text>
            <VStack pl={2} alignItems="left">
              {schemaHandler.result?.map((n) => (
                <Link to={`/content-type/${n}`}>{n}</Link>
              ))}
            </VStack>
          </Box>
          <Box ml={250} px={4}>
            {children}
          </Box>
        </Box>
      )}
    </>
  );
}
