"use client";

import { useParams, useRouter } from "next/navigation";
import { useState } from "react";

import { alpha, Button, Stack, Typography, useTheme } from "@mui/material";
import { useQuery } from "@tanstack/react-query";
import { HiPencil, HiTrash } from "react-icons/hi";

import { getAlertRuleById } from "@/api/alertRule";
import AlertRuleModal from "@/app/[locale]/alert-rule/AlertRuleModal";
import DeleteAlertRuleModal from "@/app/[locale]/alert-rule/DeleteAlertRuleModal";
import AlertRuleStatus from "@/components/AlertRule/AlertRuleStatus";
import { ALERT_RULE_VARIANTS } from "@/utils/alertRuleUtils";

export default function ViewAlertRule() {
  const { alertId } = useParams<{ alertId: string }>();
  const router = useRouter();
  const { palette } = useTheme();
  const [currentOpenModal, setCurrentOpenModal] = useState<"DELETE" | "EDIT" | null>(null);

  function handleAfterDelete() {
    router.push("/alert-rule");
  }

  const { data, refetch } = useQuery({
    queryKey: ["view-alert-rule", alertId],
    queryFn: () => getAlertRuleById(alertId),
    enabled: Boolean(alertId)
  });

  if (!data) {
    return null;
  }

  function handleRefreshData() {
    refetch();
  }

  console.log(data);

  const { Icon, defaultColor } = ALERT_RULE_VARIANTS[data.type];

  return (
    <>
      <Stack width="100%" bgcolor={palette.common.white} borderRadius={3} padding={3}>
        <Stack direction="row" justifyContent="space-between" alignItems="flex-start">
          <Stack direction="row" alignItems="center" spacing={2}>
            <Icon color={defaultColor} size="4rem" />
            <Stack spacing={2}>
              <Typography variant="h6">{data.name}</Typography>
              <AlertRuleStatus status={data.status_label} />
            </Stack>
          </Stack>
          <Stack direction="row-reverse" spacing={2}>
            <Button
              startIcon={<HiTrash />}
              onClick={() => setCurrentOpenModal("DELETE")}
              sx={{
                flex: 1,
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
                flex: 1,
                textTransform: "capitalize !important",
                color: palette.info.light,
                backgroundColor: alpha(palette.info.light, 0.05),
                paddingX: 2
              }}
            >
              Edit
            </Button>
          </Stack>
        </Stack>
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
    </>
  );
}
