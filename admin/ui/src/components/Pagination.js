import { ArrowLeftIcon, ArrowRightIcon } from "@chakra-ui/icons";
import { Button, HStack, IconButton } from "@chakra-ui/react";
import React from "react";

export default function Pagination({ page, max, onSelect, colorScheme }) {
  page = parseInt(page);
  const pagerCount = max < 10 ? max : 10;
  const offset =
    page - pagerCount / 2 < 0
      ? 0
      : page + pagerCount / 2 > max
      ? max - pagerCount
      : page - pagerCount / 2;
  if (max === 1 && page === 1) return null;
  return (
    <HStack mx="auto" justifyContent="center" w="100%">
      <IconButton
        colorScheme={colorScheme}
        icon={<ArrowLeftIcon />}
        variant={"ghost"}
        onClick={() => onSelect(1)}
        disabled={page <= 1}
      />
      {Array.apply(null, Array(pagerCount)).map((_, e) => (
        <Button
          colorScheme={colorScheme}
          variant={e + offset + 1 === page ? "solid" : "ghost"}
          onClick={() => onSelect(e + offset + 1)}
          key={e}
        >
          {e + offset + 1}
        </Button>
      ))}
      <IconButton
        colorScheme={colorScheme}
        icon={<ArrowRightIcon />}
        variant={"ghost"}
        onClick={() => onSelect(max)}
        disabled={page >= max}
      />
    </HStack>
  );
}
