import {
  Box,
  Button,
  HStack,
  Skeleton,
  Table,
  Tbody,
  Td,
  Text,
  Th,
  Thead,
  Tr,
} from "@chakra-ui/react";
import React, { useEffect, useState } from "react";
import { useParams } from "react-router";
import { useHistory } from "react-router-dom";
import useRequest from "../utils/useRequest";
import useRequestRunner from "../utils/useRequestRunner";

export default function ListEntries({ schema }) {
  const { model } = useParams();
  const history = useHistory();
  const contentApi = useRequest(
    `http://192.168.0.10/~bendeguz/phapi/admin/content-manager/${model}`
  );
  const [page, setPage] = useState(1);
  const [entryCount, setEntryCount] = useState(0);

  const contentHandler = useRequestRunner(contentApi.list, []);
  useEffect(() => {
    (async () => {
      const count = await contentHandler.runError(
        contentHandler.runStatus(contentApi.get("count"))
      );
      if (count == null) return;
      setEntryCount(count);
      contentHandler.run();
    })();
    //eslint-disable-next-line
  }, []);

  const displayFormatter = {
    timestamp: false,
    password: false,
    relation_one: false,
    relation_many: false,
    json: false,
    bool: (v) => (v ? "true" : "false"),
    default: (v) => v,
  };

  const names =
    schema &&
    Object.keys(schema.fields).filter(
      (n) =>
        displayFormatter[schema.fields[n].type] !== false &&
        !schema.fields[n].private
    );

  return (
    <Box>
      <Skeleton isLoaded={!contentHandler.isLoading}>
        <Text fontSize={40} mb={4}>
          {schema.name}
        </Text>
        <HStack mb={6}>
          {schema.editable && (
            <Button
              colorScheme="red"
              aria-label="Search database"
              onClick={() =>
                history.push(history.location.pathname + "/create")
              }
              variant="link"
            >
              New
            </Button>
          )}
        </HStack>
        <Table>
          <Thead>
            <Tr>
              {names.map((n) => (
                <Th key={n}>{n}</Th>
              ))}
            </Tr>
          </Thead>
          <Tbody>
            {contentHandler.result &&
              contentHandler.result.map((e, i) => (
                <Tr
                  key={e[schema.primary] || i}
                  onClick={() =>
                    schema.editable &&
                    history.push(
                      history.location.pathname + "/" + e[schema.primary]
                    )
                  }
                >
                  {names.map((n) => (
                    <Td key={n}>
                      {(
                        displayFormatter[schema.fields[n].type] ||
                        displayFormatter.default
                      )(e[n])}
                    </Td>
                  ))}
                </Tr>
              ))}
          </Tbody>
        </Table>
      </Skeleton>
    </Box>
  );
}
