import { Box, Text } from "@chakra-ui/react";
import React from "react";

export default function Layout({ children }) {
  return (
    <Box minH="100%" w="100%">
      <Box
        position="fixed"
        left={0}
        p={3}
        w="250px"
        top={0}
        minH="100%"
        bg="red.600"
        color="white"
      >
        <Text fontSize={32}>Phapi</Text>
      </Box>
      <Box ml={264} padding={8}>
        {children}
      </Box>
    </Box>
  );
}
