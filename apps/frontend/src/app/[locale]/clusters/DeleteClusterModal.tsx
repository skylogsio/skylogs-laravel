import { Grid2 as Grid, Typography } from "@mui/material";
import { useMutation } from "@tanstack/react-query";
import { toast } from "react-toastify";

import type { ICluster } from "@/@types/cluster";
import { deleteCluster } from "@/api/cluster";
import DeleteModalContainer from "@/components/DeleteModal/DeleteModalContainer";
import type { DeleteModalProps } from "@/components/DeleteModal/DeleteModalTypes";

export default function DeleteClusterModal({
  data,
  onAfterDelete,
  ...props
}: DeleteModalProps & { data: ICluster }) {
  const { id, name, type, url } = data;

  const { mutate: deleteClusterMutation, isPending } = useMutation({
    mutationFn: () => deleteCluster(id),
    onSuccess() {
      onAfterDelete?.();
      toast.success("Cluster Deleted Successfully.");
    }
  });

  return (
    <DeleteModalContainer {...props} onAfterDelete={deleteClusterMutation} isLoading={isPending}>
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
          {type}
        </Typography>
      </Grid>
      <Grid size={{ xs: 4, sm: 4, md: 3, lg: 2, xl: 2 }}>
        <Typography variant="body1" fontWeight="bold">
          URL:
        </Typography>
      </Grid>
      <Grid size={{ xs: 8, sm: 8, md: 9, lg: 10, xl: 10 }}>
        <Typography variant="body1" fontWeight="normal">
          {url}
        </Typography>
      </Grid>
    </DeleteModalContainer>
  );
}
