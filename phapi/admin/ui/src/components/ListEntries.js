import {
  Box,
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
import useRequest from "../utils/useRequest";
import useRequestRunner from "../utils/useRequestRunner";

export default function ListEntries({ schema }) {
  const { model } = useParams();
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
      <Text fontSize={40} mb={4}>
        {schema.name}
      </Text>
      <Skeleton isLoaded={!contentHandler.isLoading}>
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
                <Tr key={e.id || i}>
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
