import { Grid2 as Grid, Typography } from "@mui/material";
import { useMutation } from "@tanstack/react-query";
import { toast } from "react-toastify";

import type { IDataSource } from "@/@types/dataSource";
import { deleteDataSource } from "@/api/dataSource";
import ConnectionStatus from "@/components/ConnectionStatus";
import DataSourceType from "@/components/DataSource/DataSourceType";
import DeleteModalContainer from "@/components/DeleteModal/DeleteModalContainer";
import type { DeleteModalProps } from "@/components/DeleteModal/DeleteModalTypes";

export default function DeleteDataSourceModal({
  data,
  onAfterDelete,
  ...props
}: DeleteModalProps & { data: IDataSource }) {
  const { id, name, type, status } = data;

  const { mutate: deleteDataSourceMutation, isPending } = useMutation({
    mutationFn: () => deleteDataSource(id),
    onSuccess() {
      onAfterDelete?.();
      toast.success("DataSource Deleted Successfully.");
    }
  });

  return (
    <DeleteModalContainer {...props} onAfterDelete={deleteDataSourceMutation} isLoading={isPending}>
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
      <Grid
        display="flex"
        justifyContent="flex-start"
        size={{ xs: 8, sm: 8, md: 9, lg: 10, xl: 10 }}
      >
        <DataSourceType type={type} />
      </Grid>
      <Grid size={{ xs: 4, sm: 4, md: 3, lg: 3, xl: 3 }}>
        <Typography variant="body1" fontWeight="bold">
          Status:
        </Typography>
      </Grid>
      <Grid size={{ xs: 8, sm: 8, md: 9, lg: 9, xl: 9 }}>
        <ConnectionStatus status={status} />
      </Grid>
    </DeleteModalContainer>
  );
}
