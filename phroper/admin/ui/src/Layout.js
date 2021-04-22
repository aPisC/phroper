import {
  Avatar,
  Box,
  Button,
  Center,
  HStack,
  Spinner,
  Text,
  VStack,
} from "@chakra-ui/react";
import React, { useContext, useEffect } from "react";
import { Link } from "react-router-dom";
import { AuthConext } from "./auth/auth";
import useRequest from "./utils/useRequest";
import useRequestRunner from "./utils/useRequestRunner";

export default function Layout({ children }) {
  const schemaApi = useRequest(`/admin/content-schema/models`);
  const schemaHandler = useRequestRunner(schemaApi.list);
  const auth = useContext(AuthConext);
  useEffect(schemaHandler.run, [auth.user]);

  return (
    <>
      {schemaHandler.isLoading && (
        <Center w="100vw" h="100vh">
          <Spinner></Spinner>
        </Center>
      )}
      <Box bg="gray.100" minH="100vh" w="100%" overflow="visible">
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
          <Box fontSize={40} mb={6} w="100%" textAlign="center">
            <Link to="/">Phroper</Link>
          </Box>

          {auth.user && (
            <HStack mb={6}>
              <Avatar mr={4} />
              <VStack flex={1} alignItems="flex-start">
                <Text fontSize={20}>{auth.user.username}</Text>
                <Button
                  variant="link"
                  colorScheme="white"
                  onClick={auth.logout}
                >
                  logout
                </Button>
              </VStack>
            </HStack>
          )}

          {schemaHandler.isSuccess && (
            <>
              <Text fontSize={24} mb={2}>
                Content types
              </Text>
              <VStack pl={4} alignItems="flex-start">
                {schemaHandler.result
                  ?.filter((model) => model.visible)
                  .map((model) => (
                    <Link key={model.key} to={`/content-type/${model.key}`}>
                      {model.name}
                    </Link>
                  ))}
              </VStack>
            </>
          )}
        </Box>
        <Box ml={250} px={4}>
          {children}
        </Box>
      </Box>
    </>
  );
}
