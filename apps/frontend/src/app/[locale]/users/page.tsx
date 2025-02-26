"use client";
import { useRef, useState } from "react";

import { alpha, Chip } from "@mui/material";

import type { IUser } from "@/@types/user";
import ChangePasswordModal from "@/app/[locale]/users/ChangePasswordModal";
import EditUserModal from "@/app/[locale]/users/EditUserModal";
import ActionColumn from "@/components/ActionColumn";
import Table from "@/components/Table";
import type { TableComponentRef } from "@/components/Table/types";
import { ROLE_COLORS } from "@/utils/userUtils";

import CreateUserModal from "./CreateUserModal";

export default function Users() {
  const tableRef = useRef<TableComponentRef>(null);
  const [openCreateModal, setOpenCreateModal] = useState(false);
  const [editModalUserData, setEditModalUserData] = useState<IUser | null>(null);
  const [selectedUserToChangePassword, setSelectedUserToChangePassword] = useState<string | null>(
    null
  );

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
          {
            header: "Role",
            cell: ({ row }) =>
              row.original.roles.map((item, index) => (
                <Chip
                  key={index}
                  label={item}
                  sx={{
                    textTransform: "capitalize",
                    color: ROLE_COLORS[item],
                    backgroundColor: alpha(ROLE_COLORS[item], 0.1)
                  }}
                />
              ))
          },
          {
            header: "Action",
            cell: ({ row }) => (
              <ActionColumn
                onEdit={() => setEditModalUserData(row.original)}
                onChangePassword={() => setSelectedUserToChangePassword(row.original.id)}
                onDelete={() => {}}
              />
            )
          }
        ]}
        onCreate={() => setOpenCreateModal(true)}
      />
      {openCreateModal && (
        <CreateUserModal
          open={openCreateModal}
          onClose={() => setOpenCreateModal(false)}
          onSubmit={handleRefreshData}
        />
      )}
      {editModalUserData && (
        <EditUserModal
          open={!!editModalUserData}
          onClose={() => setEditModalUserData(null)}
          onSubmit={handleRefreshData}
          userData={editModalUserData}
        />
      )}
      {selectedUserToChangePassword && (
        <ChangePasswordModal
          open={!!selectedUserToChangePassword}
          onClose={() => setSelectedUserToChangePassword(null)}
          userId={selectedUserToChangePassword}
        />
      )}
    </>
  );
}
