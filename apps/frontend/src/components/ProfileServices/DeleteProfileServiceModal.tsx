import { Chip, Grid2 as Grid, Stack, Typography } from "@mui/material";
import { useMutation } from "@tanstack/react-query";
import { toast } from "react-toastify";

import type { IProfileService } from "@/@types/profileService";
import { deleteProfileService } from "@/api/profileService";
import DeleteModalContainer from "@/components/DeleteModal/DeleteModalContainer";
import type { DeleteModalProps } from "@/components/DeleteModal/DeleteModalTypes";

export default function DeleteProfileServiceModal({
  data,
  onAfterDelete,
  ...props
}: DeleteModalProps & { data: IProfileService }) {
  const {
    id,
    name,
    envs,
    user: { username },
    createdAlertRuleIds
  } = data;

  const { mutate: deletesProfileServiceMutation, isPending } = useMutation({
    mutationFn: () => deleteProfileService(id),
    onSuccess() {
      onAfterDelete?.();
      toast.success("Profile Service Deleted Successfully.");
    }
  });

  return (
    <DeleteModalContainer
      {...props}
      onAfterDelete={deletesProfileServiceMutation}
      isLoading={isPending}
    >
      <Grid size={12}>
        <Stack width="100%" spacing={2}>
          <Stack direction="row" justifyContent="flex-start" spacing={2}>
            <Typography variant="body1" fontWeight="bold">
              Name:
            </Typography>
            <Typography variant="body1" fontWeight="normal">
              {name}
            </Typography>
          </Stack>
          <Stack direction="row" justifyContent="flex-start" spacing={2}>
            <Typography variant="body1" fontWeight="bold">
              Username:
            </Typography>
            <Typography variant="body1" fontWeight="normal">
              {username}
            </Typography>
          </Stack>
          <Stack direction="row" justifyContent="flex-start" spacing={2}>
            <Typography variant="body1" fontWeight="bold">
              envs:
            </Typography>
            <Stack direction="row" spacing={2} justifyContent="center" flexWrap="wrap">
              {envs.map((env) => (
                <Chip key={env} label={env} size="small" />
              ))}
            </Stack>
          </Stack>
          <Stack direction="row" justifyContent="flex-start" spacing={2}>
            <Typography variant="body1" fontWeight="bold">
              Alert Rules Count:
            </Typography>
            <Typography variant="body1" fontWeight="normal">
              {createdAlertRuleIds?.length ?? 0}
            </Typography>
          </Stack>
        </Stack>
      </Grid>
    </DeleteModalContainer>
  );
}
