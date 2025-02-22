"use client";
import { useRef, useState } from "react";

import type { IUser } from "@/@types/user";
import ActionColumn from "@/components/ActionColumn";
import Table from "@/components/Table";
import type { TableComponentRef } from "@/components/Table/types";

import CreateUserModal from "./CreateUserModal";

export default function Users() {
  const tableRef = useRef<TableComponentRef>(null);

  const [open, setOpen] = useState(false);

  function handleOpen() {
    setOpen(true);
  }

  function handleClose() {
    setOpen(false);
  }

  function handleRefreshData() {
    if (tableRef.current) {
      tableRef.current.refreshData();
    }
  }

  return (
    <>
      <Table<IUser>
        ref={tableRef}
        title="Users"
        url="user"
        defaultPage={1}
        defaultPageSize={10}
        columns={[
          { header: "Row", accessorFn: (_, index) => index },
          { header: "Username", accessorKey: "username" },
          { header: "Full Name", accessorKey: "name" },
          { header: "Role", accessorKey: "price" },
          { header: "Created At", accessorKey: "created_at" },
          { header: "Updated At", accessorKey: "updated_at" },
          {
            header: "Action",
            cell: () => <ActionColumn onEdit={() => {}} onDelete={() => {}} />
          }
        ]}
        onCreate={handleOpen}
      />
      {open && <CreateUserModal open={open} onClose={handleClose} onSubmit={handleRefreshData} />}
    </>
  );
}
