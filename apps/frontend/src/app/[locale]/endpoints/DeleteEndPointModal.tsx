import { Grid2 as Grid, Typography } from "@mui/material";
import { useMutation } from "@tanstack/react-query";
import { toast } from "react-toastify";

import type { IEndpoint } from "@/@types/endpoint";
import { deleteEndpoint } from "@/api/endpoint";
import DeleteModalContainer from "@/components/DeleteModal/DeleteModalContainer";
import type { DeleteModalProps } from "@/components/DeleteModal/DeleteModalTypes";
import { renderEndPointChip } from "@/utils/endpointVariants";

export default function DeleteEndPointModal({
  data,
  onAfterDelete,
  ...props
}: DeleteModalProps & { data: IEndpoint }) {
  const { id, name, value, type, chatId } = data;

  const { mutate: deleteEndpointMutation, isPending } = useMutation({
    mutationFn: () => deleteEndpoint(id),
    onSuccess() {
      onAfterDelete?.();
      toast.success("EndPoint Deleted Successfully.");
    }
  });

  return (
    <DeleteModalContainer {...props} onAfterDelete={deleteEndpointMutation} isLoading={isPending}>
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
      <Grid size={{ xs: 4, sm: 4, md: 3, lg: 2, xl: 2 }}>
        <Typography variant="body1" fontWeight="bold">
          Type:
        </Typography>
      </Grid>
      <Grid size={{ xs: 8, sm: 8, md: 9, lg: 10, xl: 10 }}>
        <Typography variant="body1" fontWeight="normal">
          {renderEndPointChip(type, "small")}
        </Typography>
      </Grid>
      <Grid size={{ xs: 4, sm: 4, md: 3, lg: 2, xl: 2 }}>
        <Typography variant="body1" fontWeight="bold">
          Value:
        </Typography>
      </Grid>
      <Grid size={{ xs: 8, sm: 8, md: 9, lg: 10, xl: 10 }}>
        <Typography variant="body1" fontWeight="normal">
          {type === "telegram" ? chatId : value}
        </Typography>
      </Grid>
    </DeleteModalContainer>
  );
}
