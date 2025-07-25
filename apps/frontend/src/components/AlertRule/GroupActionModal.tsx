import React, { useState } from "react";

import {
  alpha,
  Button,
  Chip,
  Divider,
  MenuItem,
  Stack,
  TextField,
  Typography,
  useTheme
} from "@mui/material";
import { useQuery } from "@tanstack/react-query";
import { IoNotifications, IoNotificationsOff } from "react-icons/io5";

import { getAlertRuleCreateData } from "@/api/alertRule";
import ModalContainer from "@/components/Modal";
import { ModalContainerProps } from "@/components/Modal/types";

interface GroupActionModalProps extends Pick<ModalContainerProps, "open" | "onClose"> {}

export default function GroupActionModal({ open, onClose }: GroupActionModalProps) {
  const { palette } = useTheme();
  const [selectedEndpointIds, setSelectedEndpointIds] = useState<string[]>([]);
  const [selectedUsers, setSelectedUsers] = useState<string[]>([]);

  const { data } = useQuery({
    queryKey: ["alert-rule-create-data"],
    queryFn: () => getAlertRuleCreateData()
  });

  const handleRemoveEndpointChip = (endpointId: string) => {
    setSelectedEndpointIds((prev) => prev.filter((id) => id !== endpointId));
  };

  const renderEndpointChips = (selectedIds: unknown) => {
    const selected =
      data?.endpoints.filter((endpoint) => (selectedIds as string[]).includes(endpoint.id)) ?? [];
    return (
      <Stack
        gap={1}
        direction="row"
        flexWrap="wrap"
        justifyContent="flex-start"
        sx={{ float: "left" }}
        onMouseDown={(event) => event.stopPropagation()}
      >
        {selected.map((endpoint) => (
          <Chip
            key={endpoint.id}
            label={endpoint.name}
            size="small"
            onDelete={() => handleRemoveEndpointChip(endpoint.id)}
          />
        ))}
      </Stack>
    );
  };

  const handleRemoveUserChip = (userId: string) => {
    setSelectedUsers((prev) => prev.filter((id) => id !== userId));
  };

  const renderUserChips = (selectedIds: unknown) => {
    const selected =
      data?.users.filter((user) => (selectedIds as string[]).includes(user.id)) ?? [];
    return (
      <Stack
        gap={1}
        direction="row"
        flexWrap="wrap"
        justifyContent="flex-start"
        sx={{ float: "left" }}
        onMouseDown={(event) => event.stopPropagation()}
      >
        {selected.map((user) => (
          <Chip
            key={user.id}
            label={user.name}
            size="small"
            onDelete={() => handleRemoveUserChip(user.id)}
          />
        ))}
      </Stack>
    );
  };

  function handleSubmit() {
    console.log(selectedUsers, selectedEndpointIds);
  }

  return (
    <ModalContainer title="Group Actions" open={open} onClose={onClose} disableEscapeKeyDown>
      <Stack>
        <Stack direction="row-reverse" spacing={1} mt={2}>
          <Button
            startIcon={<IoNotificationsOff size="1.4rem" />}
            sx={{
              textTransform: "capitalize !important",
              color: palette.warning.main,
              backgroundColor: alpha(palette.warning.main, 0.05),
              paddingX: 2
            }}
          >
            Silent
          </Button>
          <Button
            startIcon={<IoNotifications size="1.4rem" />}
            sx={{
              textTransform: "capitalize !important",
              color: palette.warning.main,
              backgroundColor: alpha(palette.warning.main, 0.05),
              paddingX: 2
            }}
          >
            Unsilent
          </Button>
        </Stack>
        <Divider sx={{ marginY: 3 }} />
        <Stack spacing={1}>
          <Typography variant="subtitle1" fontWeight="bold" component="div">
            Add User and Endpoint:
          </Typography>
          <TextField
            select
            label="Endpoints"
            variant="filled"
            value={selectedEndpointIds}
            onChange={(event) => setSelectedEndpointIds(event.target.value as unknown as string[])}
            slotProps={{
              select: {
                multiple: true,
                renderValue: renderEndpointChips
              }
            }}
          >
            {data?.endpoints.map((endpoint) => (
              <MenuItem key={endpoint.id} value={endpoint.id}>
                {endpoint.name}
              </MenuItem>
            ))}
          </TextField>
          <TextField
            select
            label="Users"
            variant="filled"
            value={selectedUsers}
            onChange={(event) => setSelectedUsers(event.target.value as unknown as string[])}
            slotProps={{
              select: {
                multiple: true,
                renderValue: renderUserChips
              }
            }}
          >
            {data?.users.map((user) => (
              <MenuItem key={user.id} value={user.id}>
                {user.name}
              </MenuItem>
            ))}
          </TextField>
          <Button fullWidth variant="contained" size="large" onClick={handleSubmit}>
            Submit
          </Button>
        </Stack>
      </Stack>
    </ModalContainer>
  );
}
