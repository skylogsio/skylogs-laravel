"use client";

import { useParams, useRouter } from "next/navigation";
import React, { ReactElement, useState } from "react";

import {
  alpha,
  Button,
  Chip,
  IconButton,
  Popover,
  Stack,
  Typography,
  useTheme
} from "@mui/material";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { BsFillClipboard2Fill } from "react-icons/bs";
import { FaClockRotateLeft } from "react-icons/fa6";
import { HiCollection, HiFire, HiPencil, HiTrash, HiUsers } from "react-icons/hi";
import { IoNotifications, IoNotificationsOff } from "react-icons/io5";
import { RiTestTubeFill } from "react-icons/ri";

import { type IAlertRule } from "@/@types/alertRule";
import {
  getAlertRuleById,
  getAlertRuleEndpointsList,
  getAlertRuleUsersList,
  removeEndpointFromAlertRule,
  removeUserFromAlertRule,
  silenceAlertRule,
  testAlertRule
} from "@/api/alertRule";
import AlertRuleModal from "@/app/[locale]/alert-rule/AlertRuleModal";
import DeleteAlertRuleModal from "@/app/[locale]/alert-rule/DeleteAlertRuleModal";
import AlertRuleStatus from "@/components/AlertRule/AlertRuleStatus";
import DataTable from "@/components/Table/DataTable";
import { ALERT_RULE_VARIANTS } from "@/utils/alertRuleUtils";
import { renderEndPointChip } from "@/utils/endpointVariants";

const TABS = ["fire", "users", "history", "notify"];
type TabType = (typeof TABS)[number];

const TABS_ICON: { [key: TabType]: ReactElement } = {
  fire: <HiFire size="1.2rem" />,
  users: <HiUsers size="1.2rem" />,
  history: <FaClockRotateLeft size="1.1rem" />,
  notify: <HiCollection size="1.2rem" />
};

export default function ViewAlertRule() {
  const { alertId } = useParams<{ alertId: string }>();
  const router = useRouter();
  const queryClient = useQueryClient();
  const { palette, spacing } = useTheme();
  const [testConfirmationAnchorEl, setTestConfirmationAnchorEl] =
    useState<HTMLButtonElement | null>(null);
  const [currentTab, setCurrentTab] = useState<TabType>("users");
  const [currentOpenModal, setCurrentOpenModal] = useState<"DELETE" | "EDIT" | null>(null);

  function handleAfterDelete() {
    router.push("/alert-rule");
  }

  function handleTest(event: React.MouseEvent<HTMLButtonElement>) {
    setTestConfirmationAnchorEl(event.currentTarget);
  }

  const handleCloseTestConfirmationPopover = () => {
    setTestConfirmationAnchorEl(null);
  };
  const openTestConfirmationPopover = Boolean(testConfirmationAnchorEl);
  const testConfirmationId = openTestConfirmationPopover ? "test-confirmation-popover" : undefined;

  const { data, refetch } = useQuery({
    queryKey: ["view-alert-rule", alertId],
    queryFn: () => getAlertRuleById(alertId),
    enabled: Boolean(alertId)
  });

  const { mutate: silenceAlertRuleMutation, isPending: isSilencing } = useMutation({
    mutationFn: () => silenceAlertRule(alertId),
    onSuccess: (data) => {
      if (data.status) {
        queryClient.setQueryData(["view-alert-rule", alertId], (oldData: IAlertRule) => ({
          ...oldData,
          is_silent: !oldData.is_silent
        }));
      }
    }
  });

  async function handleCopyApiTokenToClipboard() {
    try {
      await window.navigator.clipboard.writeText(data!.api_token!);
    } catch (err) {
      console.error("Unable to copy to clipboard.", err);
      alert("Copy to clipboard failed.");
    }
  }

  const { mutate: testAlertRuleMutation, isPending: isTesting } = useMutation({
    mutationFn: () => testAlertRule(alertId),
    onSuccess: (data) => {
      if (data.status) {
        handleCloseTestConfirmationPopover();
      }
    }
  });

  const { data: UsersList, refetch: refetchUsersList } = useQuery({
    queryKey: ["alert-rule-users", alertId],
    queryFn: () => getAlertRuleUsersList(alertId),
    enabled: Boolean(alertId) && currentTab === "users"
  });

  const { mutate: removeUser, isPending: isRemovingUser } = useMutation({
    mutationFn: (userId: string) => removeUserFromAlertRule(alertId, userId),
    onSuccess: (data) => {
      if (data.status) {
        refetchUsersList();
      }
    }
  });

  const { data: EndpointsList, refetch: refetchEndpointsList } = useQuery({
    queryKey: ["notifications", alertId],
    queryFn: () => getAlertRuleEndpointsList(alertId),
    enabled: Boolean(alertId) && currentTab === "notify"
  });

  const { mutate: removeEndpoint, isPending: isRemovingEndpoint } = useMutation({
    mutationFn: (endpointId: string) => removeEndpointFromAlertRule(alertId, endpointId),
    onSuccess: (data) => {
      if (data.status) {
        refetchEndpointsList();
      }
    }
  });

  function renderTab(tab: TabType) {
    let backgroundColor;
    let color;
    if (tab === "fire") {
      backgroundColor =
        currentTab === tab ? palette.error.main : alpha(palette.secondary.main, 0.1);
      color = currentTab === tab ? palette.background.paper : palette.error.main;
    } else {
      backgroundColor =
        currentTab === tab ? palette.secondary.dark : alpha(palette.secondary.main, 0.1);
      color = currentTab === tab ? palette.background.paper : palette.secondary.dark;
    }
    return (
      <Button
        key={tab}
        startIcon={TABS_ICON[tab]}
        variant="outlined"
        onClick={() => setCurrentTab(tab)}
        sx={{
          backgroundColor,
          color,
          border: "none",
          textTransform: "capitalize"
        }}
      >
        {tab}
      </Button>
    );
  }

  if (!data) {
    return null;
  }

  console.log(data);

  function handleRefreshData() {
    refetch();
  }

  const { Icon, defaultColor } = ALERT_RULE_VARIANTS[data.type];

  return (
    <>
      <Stack width="100%" alignItems="center" spacing={1}>
        <Stack width="100%" bgcolor={palette.background.paper} borderRadius={3} padding={3}>
          <Stack direction="row" justifyContent="space-between" alignItems="flex-start">
            <Stack direction="row" alignItems="center" spacing={2}>
              <Icon color={defaultColor} size="4rem" />
              <Stack alignItems="flex-start" spacing={0.5}>
                <Typography variant="h6" fontWeight="bold">
                  {data.name}
                </Typography>
                <AlertRuleStatus status={data.status_label} />
              </Stack>
            </Stack>
            <Stack spacing={1} alignItems="flex-end">
              <Stack direction="row-reverse" spacing={1}>
                <Button
                  startIcon={<HiTrash />}
                  onClick={() => setCurrentOpenModal("DELETE")}
                  sx={{
                    textTransform: "capitalize !important",
                    color: palette.error.light,
                    backgroundColor: alpha(palette.error.light, 0.05),
                    paddingX: 2
                  }}
                >
                  Delete
                </Button>
                <Button
                  startIcon={<HiPencil />}
                  onClick={() => setCurrentOpenModal("EDIT")}
                  sx={{
                    textTransform: "capitalize !important",
                    color: palette.info.light,
                    backgroundColor: alpha(palette.info.light, 0.05),
                    paddingX: 2
                  }}
                >
                  Edit
                </Button>
                <Button
                  startIcon={
                    data.is_silent ? (
                      <IoNotificationsOff size="1.4rem" />
                    ) : (
                      <IoNotifications size="1.4rem" />
                    )
                  }
                  disabled={isSilencing}
                  onClick={() => silenceAlertRuleMutation()}
                  sx={{
                    textTransform: "capitalize !important",
                    color: palette.warning.main,
                    backgroundColor: alpha(palette.warning.main, 0.05),
                    paddingX: 2
                  }}
                >
                  {data.is_silent ? "Unsilent" : "Silent"}
                </Button>
                <Button
                  onClick={handleTest}
                  startIcon={<RiTestTubeFill size="1.4rem" />}
                  sx={{
                    textTransform: "capitalize !important",
                    color: palette.primary.light,
                    backgroundColor: alpha(palette.primary.light, 0.05),
                    paddingX: 2
                  }}
                >
                  Test
                </Button>
              </Stack>
              {data.api_token && (
                <Stack
                  direction="row"
                  alignItems="center"
                  bgcolor={alpha(palette.secondary.main, 0.1)}
                  borderRadius={2}
                  paddingLeft={1}
                  paddingY={0.5}
                  paddingRight={0.5}
                  spacing={1}
                  border="1px solid"
                  borderColor={palette.secondary.light}
                >
                  <Typography
                    variant="caption"
                    color={palette.secondary.main}
                    sx={{
                      maxWidth: "300px",
                      textWrap: "nowrap",
                      overflow: "hidden",
                      textOverflow: "ellipsis"
                    }}
                  >
                    {data.api_token}
                  </Typography>
                  <IconButton size="small" onClick={() => handleCopyApiTokenToClipboard()}>
                    <BsFillClipboard2Fill size="1rem" color={palette.secondary.main} />
                  </IconButton>
                </Stack>
              )}
            </Stack>
          </Stack>
          <Stack marginTop={3}>
            <Stack direction="row" spacing={1} alignItems="center">
              <Typography variant="subtitle1" fontWeight="bold">
                Owner :
              </Typography>
              <Typography>---</Typography>
            </Stack>
            <Stack direction="row" spacing={1} alignItems="center">
              <Typography variant="subtitle1" fontWeight="bold">
                Tags :
              </Typography>
              <Stack padding={1} direction="row" gap={1} flexWrap="wrap">
                {data.tags.map((tag, index) => (
                  <Chip key={index} variant="filled" label={tag} size="small" />
                ))}
              </Stack>
            </Stack>
          </Stack>
        </Stack>
        <Stack
          direction="row"
          spacing={1}
          borderRadius={3}
          padding={2}
          sx={{
            backgroundColor: palette.background.paper,
            padding: `${spacing(1)}!important`,
            marginTop: `${spacing(2)}!important`
          }}
        >
          {TABS.map((tab) => renderTab(tab))}
        </Stack>
        {currentTab === "users" && (
          <DataTable
            data={UsersList?.alertUsers ?? []}
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
        )}
        {currentTab === "notify" && (
          <DataTable
            data={EndpointsList?.alertEndpoints ?? []}
            columns={[
              { header: "Row", size: 50, accessorFn: (_, index) => ++index },
              { header: "Name", accessorKey: "name" },
              {
                header: "Type",
                accessorKey: "type",
                cell: ({ cell }) => renderEndPointChip(cell.getValue())
              },
              {
                header: "Actions",
                cell: ({ row }) => (
                  <IconButton
                    disabled={isRemovingEndpoint}
                    onClick={() => removeEndpoint(row.original.id)}
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
        )}
      </Stack>
      {currentOpenModal === "DELETE" && (
        <DeleteAlertRuleModal
          open={currentOpenModal === "DELETE"}
          onClose={() => setCurrentOpenModal(null)}
          onAfterDelete={handleAfterDelete}
          data={data}
        />
      )}
      {currentOpenModal === "EDIT" && (
        <AlertRuleModal
          open={currentOpenModal === "EDIT"}
          onClose={() => setCurrentOpenModal(null)}
          data={data}
          onSubmit={handleRefreshData}
        />
      )}
      <Popover
        id={testConfirmationId}
        open={openTestConfirmationPopover}
        anchorEl={testConfirmationAnchorEl}
        onClose={handleCloseTestConfirmationPopover}
        anchorOrigin={{
          vertical: "bottom",
          horizontal: "center"
        }}
        transformOrigin={{
          vertical: "top",
          horizontal: "center"
        }}
      >
        <Stack spacing={3} padding={2}>
          <Typography variant="subtitle1">
            Are you sure about <strong>Testing</strong> this Alert?
          </Typography>
          <Stack direction="row-reverse" spacing={1}>
            <Button
              size="small"
              variant="contained"
              onClick={() => testAlertRuleMutation()}
              disabled={isTesting}
              sx={{ flex: 1 }}
            >
              Test
            </Button>
            <Button
              onClick={handleCloseTestConfirmationPopover}
              size="small"
              variant="outlined"
              disabled={isTesting}
              sx={{ flex: 1 }}
            >
              Cancel
            </Button>
          </Stack>
        </Stack>
      </Popover>
    </>
  );
}
