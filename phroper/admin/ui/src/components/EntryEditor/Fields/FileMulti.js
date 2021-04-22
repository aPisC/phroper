import {
  Accordion,
  AccordionButton,
  AccordionIcon,
  AccordionItem,
  AccordionPanel,
  Box,
  Button,
  FormControl,
  Grid,
  GridItem,
} from "@chakra-ui/react";
import { connect } from "formik";
import FileOne from "./FileOne";

function FileMulti({ formik, schema, onChange }) {
  const name = schema.key;
  console.log(formik);

  let value = formik.values[name];
  value = Array.isArray(value) ? value : [];

  return (
    <GridItem colSpan={4}>
      <FormControl minW="20%">
        <Accordion allowMultiple>
          <AccordionItem>
            <h2>
              <AccordionButton>
                <Box flex="1" textAlign="left">
                  {schema.name}
                </Box>
                <AccordionIcon />
              </AccordionButton>
            </h2>
            <AccordionPanel pb={4}>
              <Grid
                templateColumns={{
                  sm: "repeat(1, 1fr)",
                  lg: "repeat(2, 1fr)",
                  "2xl": "repeat(3, 1fr)",
                }}
                flex={1}
                gap={6}
                mb={6}
              >
                {value.map((v, i) => (
                  <FileOne
                    key={i}
                    value={v}
                    name={`${name}[${i}]`}
                    onChange={(event) =>
                      formik.setFieldValue(
                        event.target.name,
                        event.target.value
                      )
                    }
                  />
                ))}
              </Grid>
              <Button
                onClick={() =>
                  formik.setFieldValue(`${name}[${value.length}]`, null)
                }
              >
                New
              </Button>
            </AccordionPanel>
          </AccordionItem>
        </Accordion>
      </FormControl>
    </GridItem>
  );
}

export default connect(FileMulti);
