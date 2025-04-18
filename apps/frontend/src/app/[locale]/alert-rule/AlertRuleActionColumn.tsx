import { useState } from "react";

import { alpha, Button, IconButton, Popover, Stack, Typography } from "@mui/material";
import { useMutation } from "@tanstack/react-query";
import { HiDotsHorizontal } from "react-icons/hi";
import { IoNotifications, IoNotificationsOff } from "react-icons/io5";
import { RiTestTubeFill } from "react-icons/ri";

import { silenceAlertRule, testAlertRule } from "@/api/alertRule";
import ActionColumn, { ActionColumnProps } from "@/components/ActionColumn";

import AlertRuleUserModal from "./UserModal";

interface AlertRuleActionColumnProps extends ActionColumnProps {
  rowId: string;
  isSilent?: boolean;
}

export default function AlertRuleActionColumn({
  rowId,
  isSilent,
  onEdit,
  onDelete
}: AlertRuleActionColumnProps) {
  const [testConfirmationAnchorEl, setTestConfirmationAnchorEl] =
    useState<HTMLButtonElement | null>(null);
  const [showMoreAnchorEl, setShowMoreAnchorEl] = useState<HTMLButtonElement | null>(null);
  const [isSilentStatus, setIsSilentStatus] = useState<boolean>(Boolean(isSilent));

  const handleCloseTestConfirmationPopover = () => {
    setTestConfirmationAnchorEl(null);
  };

  function handleTest(event: React.MouseEvent<HTMLButtonElement>) {
    setTestConfirmationAnchorEl(event.currentTarget);
  }

  const openTestConfirmationPopover = Boolean(testConfirmationAnchorEl);
  const testConfirmationId = openTestConfirmationPopover ? "test-confirmation-popover" : undefined;

  const { mutate: silenceAlertRuleMutation, isPending: isSilencing } = useMutation({
    mutationFn: () => silenceAlertRule(rowId),
    onSuccess: (data) => {
      if (data.status) {
        setIsSilentStatus((prev) => !prev);
      }
    }
  });

  const { mutate: testAlertRuleMutation, isPending: isTesting } = useMutation({
    mutationFn: () => testAlertRule(rowId),
    onSuccess: (data) => {
      if (data.status) {
        handleCloseTestConfirmationPopover();
      }
    }
  });

  const handleShowMorePopoverOpen = (event: React.MouseEvent<HTMLButtonElement>) => {
    setShowMoreAnchorEl(event.currentTarget);
  };

  const handleShowMorePopoverClose = () => {
    setShowMoreAnchorEl(null);
  };

  const openShowMorePopover = Boolean(showMoreAnchorEl);
  const showMorePopoverId = openShowMorePopover ? "show-more-popover" : undefined;

  return (
    <>
      <Stack direction="row" justifyContent="center" spacing={1}>
        <IconButton
          onClick={handleTest}
          sx={({ palette }) => ({
            color: palette.primary.light,
            backgroundColor: alpha(palette.primary.light, 0.05)
          })}
        >
          <RiTestTubeFill size="1.4rem" />
        </IconButton>
        {isSilentStatus ? (
          <IconButton
            onClick={() => silenceAlertRuleMutation()}
            disabled={isSilencing}
            sx={({ palette }) => ({
              color: palette.warning.main,
              backgroundColor: alpha(palette.warning.main, 0.05)
            })}
          >
            <IoNotificationsOff size="1.4rem" />
          </IconButton>
        ) : (
          <IconButton
            onClick={() => silenceAlertRuleMutation()}
            disabled={isSilencing}
            sx={({ palette }) => ({
              color: palette.warning.main,
              backgroundColor: alpha(palette.warning.main, 0.05)
            })}
          >
            <IoNotifications size="1.4rem" />
          </IconButton>
        )}
        <IconButton
          sx={{ backgroundColor: ({ palette }) => alpha(palette.secondary.light, 0.2) }}
          onClick={handleShowMorePopoverOpen}
        >
          <HiDotsHorizontal size="1.3rem" />
        </IconButton>
      </Stack>
      <Popover
        id={showMorePopoverId}
        open={openShowMorePopover}
        anchorEl={showMoreAnchorEl}
        onClose={handleShowMorePopoverClose}
        anchorOrigin={{
          vertical: "bottom",
          horizontal: "center"
        }}
        transformOrigin={{
          vertical: "top",
          horizontal: "center"
        }}
      >
        <Stack padding={1} direction="row" gap={1} flexWrap="wrap" maxWidth={300}>
          <ActionColumn onEdit={onEdit} onDelete={onDelete} />
          <AlertRuleUserModal alertId={rowId} />
        </Stack>
      </Popover>
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
