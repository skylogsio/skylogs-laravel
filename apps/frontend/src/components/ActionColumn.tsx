import { useState } from "react";

import { alpha, Button, IconButton, Popover, Stack, Typography } from "@mui/material";
import { useMutation } from "@tanstack/react-query";
import { HiKey, HiPencil, HiTrash } from "react-icons/hi";
import { IoNotifications, IoNotificationsOff } from "react-icons/io5";
import { RiTestTubeFill } from "react-icons/ri";

import { silenceAlertRule, testAlertRule } from "@/api/alertRule";

interface ActionColumnProps {
  rowId?: string | number;
  onEdit?: () => void;
  onDelete?: () => void;
  onChangePassword?: () => void;
  hasTest?: boolean;
  hasSilent?: boolean;
  isSilent?: boolean;
}

export default function ActionColumn({
  rowId,
  onEdit,
  onDelete,
  onChangePassword,
  hasTest,
  hasSilent,
  isSilent
}: ActionColumnProps) {
  const [testConfirmationAnchorEl, setTestConfirmationAnchorEl] =
    useState<HTMLButtonElement | null>(null);
  const [isSilentStatus, setIsSilentStatus] = useState<boolean>(Boolean(isSilent));

  const handleCloseTestConfirmationPopover = () => {
    setTestConfirmationAnchorEl(null);
  };

  function handleTest(event: React.MouseEvent<HTMLButtonElement>) {
    setTestConfirmationAnchorEl(event.currentTarget);
  }

  const openTestConfirmationPopover = Boolean(testConfirmationAnchorEl);
  const testConfirmationId = openTestConfirmationPopover ? "test-confirmation-popover" : undefined;

  const { mutate: testAlertRuleMutation, isPending: isTesting } = useMutation({
    mutationFn: () => testAlertRule(rowId),
    onSuccess: (data) => {
      if (data.status) {
        handleCloseTestConfirmationPopover();
      }
    }
  });

  const { mutate: silenceAlertRuleMutation, isPending: isSilencing } = useMutation({
    mutationFn: () => silenceAlertRule(rowId),
    onSuccess: (data) => {
      if (data.status) {
        setIsSilentStatus((prev) => !prev);
      }
    }
  });

  return (
    <>
      <Stack direction="row" spacing={1} justifyContent="center">
        {hasTest && (
          <IconButton
            onClick={handleTest}
            sx={({ palette }) => ({
              color: palette.primary.light,
              backgroundColor: alpha(palette.primary.light, 0.05)
            })}
          >
            <RiTestTubeFill size="1.4rem" />
          </IconButton>
        )}
        {hasSilent &&
          (isSilentStatus ? (
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
          ) : (
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
          ))}
        {onEdit && (
          <IconButton
            onClick={onEdit}
            sx={({ palette }) => ({
              color: palette.info.light,
              backgroundColor: alpha(palette.info.light, 0.05)
            })}
          >
            <HiPencil size="1.4rem" />
          </IconButton>
        )}
        {onChangePassword && (
          <IconButton
            onClick={onChangePassword}
            sx={({ palette }) => ({
              color: palette.secondary.main,
              backgroundColor: alpha(palette.secondary.dark, 0.05)
            })}
          >
            <HiKey size="1.3rem" />
          </IconButton>
        )}
        {onDelete && (
          <IconButton
            onClick={onDelete}
            sx={({ palette }) => ({
              color: palette.error.light,
              backgroundColor: alpha(palette.error.light, 0.05)
            })}
          >
            <HiTrash size="1.4rem" />
          </IconButton>
        )}
      </Stack>
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
