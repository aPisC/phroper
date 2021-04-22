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
import Pagination from "./Pagination";

export default function ListEntries({ schema }) {
  const { model } = useParams();
  const history = useHistory();
  const page = new URLSearchParams(history.location.search).get("page") || 1;

  const contentApi = useRequest(`/admin/content-manager/${model}`);
  const [entryCount, setEntryCount] = useState(0);

  const contentHandler = useRequestRunner(contentApi.list, []);
  useEffect(() => {
    (async () => {
      const count = await contentHandler.runError(
        contentHandler.runStatus(contentApi.get("count"))
      );
      if (count == null) return;
      setEntryCount(count);
      contentHandler.run(
        contentApi.send(`?_limit=100&_start=${(page - 1) * 100}`)
      );
    })();
    //eslint-disable-next-line
  }, [page, schema]);

  const displayFormatter = {
    //timestamp: true,
    password: false,
    relation_one: false,
    relation_many: false,
    file: false,
    json: false,
    bool: (v) => (v ? "true" : "false"),
    default: (v) => String(v),
    file_multi: false,
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
        <Pagination
          page={page}
          max={Math.ceil(entryCount / 100) || 1}
          colorScheme="red"
          onSelect={(page) => history.push("?page=" + page)}
        />
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
        <Table mb={6}>
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
        <Pagination
          page={page}
          max={Math.ceil(entryCount / 100) || 1}
          colorScheme="red"
          onSelect={(page) => history.push("?page=" + page)}
        />
      </Skeleton>
    </Box>
  );
}
