import { Grid2 as Grid, Typography } from "@mui/material";
import { useMutation } from "@tanstack/react-query";
import { toast } from "react-toastify";

import type { IAlertRule } from "@/@types/alertRule";
import { deleteAlertRule } from "@/api/alertRule";
import AlertRuleStatus from "@/components/AlertRule/AlertRuleStatus";
import AlertRuleType from "@/components/AlertRule/AlertRuleType";
import DeleteModalContainer from "@/components/DeleteModal/DeleteModalContainer";
import type { DeleteModalProps } from "@/components/DeleteModal/DeleteModalTypes";

export default function DeleteAlertRuleModal({
  data,
  onAfterDelete,
  ...props
}: DeleteModalProps & { data: IAlertRule }) {
  const { id, name, type, status_label, count_endpoints = 0 } = data;

  const { mutate: deleteAlertRuleMutation, isPending } = useMutation({
    mutationFn: () => deleteAlertRule(id),
    onSuccess() {
      onAfterDelete?.();
      toast.success("AlertRule Deleted Successfully.");
    }
  });

  return (
    <DeleteModalContainer {...props} onAfterDelete={deleteAlertRuleMutation} isLoading={isPending}>
      <Grid size={{ xs: 4, sm: 4, md: 3, lg: 2, xl: 2 }}>
        <Typography variant="body1" fontWeight="bold">
          Name:
        </Typography>
      </Grid>
      <Grid size={{ xs: 8, sm: 8, md: 9, lg: 10, xl: 10 }}>
        <Typography variant="body1" fontWeight="normal">
          {name}
        </Typography>
      </Grid>
      <Grid size={{ xs: 4, sm: 4, md: 3, lg: 2, xl: 2 }}>Type:</Grid>
      <Grid
        size={{ xs: 8, sm: 8, md: 9, lg: 10, xl: 10 }}
        display="flex"
        justifyContent="flex-start"
      >
        <AlertRuleType type={type} />
      </Grid>
      <Grid size={{ xs: 4, sm: 4, md: 3, lg: 2, xl: 2 }}>Status:</Grid>
      <Grid
        size={{ xs: 8, sm: 8, md: 9, lg: 10, xl: 10 }}
        display="flex"
        justifyContent="flex-start"
      >
        <AlertRuleStatus status={status_label} />
      </Grid>
      <Grid size={{ xs: 4, sm: 4, md: 3, lg: 2, xl: 2 }}>
        <Typography variant="body1" fontWeight="bold">
          Notify:
        </Typography>
      </Grid>
      <Grid size={{ xs: 8, sm: 8, md: 9, lg: 10, xl: 10 }}>
        <Typography variant="body1" fontWeight="normal">
          {count_endpoints}
        </Typography>
      </Grid>
    </DeleteModalContainer>
  );
}
