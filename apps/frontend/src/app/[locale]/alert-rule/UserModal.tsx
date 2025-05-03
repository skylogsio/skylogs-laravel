import React, { useState } from "react";

import { alpha, Autocomplete, Button, IconButton, Stack, TextField } from "@mui/material";
import { useMutation, useQuery } from "@tanstack/react-query";
import { FaUsers } from "react-icons/fa";
import { HiOutlinePlusSm, HiTrash } from "react-icons/hi";

import type { IUser } from "@/@types/user";
import {
  addUsersToAlertRule,
  getAlertRuleUsersList,
  removeUserFromAlertRule
} from "@/api/alertRule/alertRule";
import ModalContainer from "@/components/Modal";
import DataTable from "@/components/Table/DataTable";

interface AlertRuleUserModalProps {
  alertId: string;
}

export default function AlertRuleUserModal({ alertId }: AlertRuleUserModalProps) {
  const [open, setOpen] = useState(false);
  const [selectedUsers, setSelectedUsers] = useState<IUser[]>([]);

  const { data: endpointsList, refetch } = useQuery({
    queryKey: ["alert-rule-users", alertId],
    queryFn: () => getAlertRuleUsersList(alertId)
  });

  const { mutate: addUsers, isPending: isAddingUsers } = useMutation({
    mutationFn: (userIds: string[]) => addUsersToAlertRule(alertId, userIds),
    onSuccess: (data) => {
      if (data.status) {
        setSelectedUsers([]);
        refetch();
      }
    }
  });

  const { mutate: removeUser, isPending: isRemovingUser } = useMutation({
    mutationFn: (userId: string) => removeUserFromAlertRule(alertId, userId),
    onSuccess: (data) => {
      if (data.status) {
        refetch();
      }
    }
  });

  function handleAddUsers() {
    const userIds = selectedUsers.map((item) => item.id);
    if (userIds.length > 0) {
      addUsers(userIds);
    }
  }

  function handleClose() {
    setOpen(false);
  }

  return (
    <>
      <IconButton
        onClick={() => setOpen(true)}
        sx={({ palette }) => ({
          color: palette.primary.light,
          backgroundColor: alpha(palette.primary.light, 0.05)
        })}
      >
        <FaUsers size="1.3rem" />
      </IconButton>
      <ModalContainer title="Users" open={open} onClose={handleClose} disableEscapeKeyDown>
        <Stack marginTop={2} spacing={1}>
          <Stack direction="row" spacing={1} alignItems="center">
            <Autocomplete
              multiple
              id="endpoints"
              size="small"
              options={endpointsList?.selectableUsers ?? []}
              getOptionLabel={(option) => option.name}
              value={selectedUsers}
              onChange={(_, value) => setSelectedUsers(value)}
              sx={{ flex: 1 }}
              renderInput={(params) => (
                <TextField
                  {...params}
                  slotProps={{
                    input: params.InputProps,
                    inputLabel: params.InputLabelProps,
                    htmlInput: params.inputProps
                  }}
                  variant="filled"
                  label="Users"
                />
              )}
            />
            <Button
              onClick={() => handleAddUsers()}
              disabled={selectedUsers.length === 0 || isAddingUsers}
              variant="contained"
              color="primary"
              size="large"
              startIcon={<HiOutlinePlusSm size="1.3rem" />}
            >
              Add
            </Button>
          </Stack>
          <DataTable
            data={endpointsList?.alertUsers ?? []}
            columns={[
              { header: "Row", size: 50, accessorFn: (_, index) => ++index },
              { header: "Username", accessorKey: "username" },
              { header: "Name", accessorKey: "name" },
              {
                header: "Actions",
                cell: ({ row }) => (
                  <IconButton
                    disabled={isRemovingUser}
                    onClick={() => removeUser(row.original.id)}
                    sx={({ palette }) => ({
                      color: palette.error.light,
                      backgroundColor: alpha(palette.error.light, 0.05)
                    })}
                  >
                    <HiTrash size="1.4rem" />
                  </IconButton>
                )
              }
            ]}
          />
        </Stack>
      </ModalContainer>
    </>
  );
}
