import React from "react";

export default function EditEntry({ isCreating, model }) {
  return <div>{isCreating ? "create" : "edit"}</div>;
}
