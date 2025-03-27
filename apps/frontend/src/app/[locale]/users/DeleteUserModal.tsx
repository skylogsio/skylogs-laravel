import { alpha, Chip, Grid2 as Grid, Typography } from "@mui/material";
import { useMutation } from "@tanstack/react-query";
import { toast } from "react-toastify";

import type { IUser } from "@/@types/user";
import { deleteUser } from "@/api/user";
import DeleteModalContainer from "@/components/DeleteModal/DeleteModalContainer";
import type { DeleteModalProps } from "@/components/DeleteModal/DeleteModalTypes";
import { ROLE_COLORS } from "@/utils/userUtils";

export default function DeleteUserModal({
  data,
  onAfterDelete,
  ...props
}: DeleteModalProps & { data: IUser }) {
  const { id, name, username, roles } = data;

  const { mutate: deleteUserMutation, isPending } = useMutation({
    mutationFn: () => deleteUser(id),
    onSuccess() {
      onAfterDelete?.();
      toast.success("User Deleted Successfully.");
    }
  });

  return (
    <DeleteModalContainer {...props} onAfterDelete={deleteUserMutation} isLoading={isPending}>
      <Grid size={{ xs: 4 }}>
        <Typography variant="body1" fontWeight="bold">
          Username:
        </Typography>
      </Grid>
      <Grid size={{ xs: 8 }}>
        <Typography variant="body1" fontWeight="normal">
          {username}
        </Typography>
      </Grid>
      <Grid size={{ xs: 4 }}>
        <Typography variant="body1" fontWeight="bold">
          Full Name:
        </Typography>
      </Grid>
      <Grid size={{ xs: 8 }}>
        <Typography variant="body1" fontWeight="normal">
          {name}
        </Typography>
      </Grid>
      <Grid size={{ xs: 4, sm: 4, md: 3, lg: 2, xl: 2 }}>
        <Typography variant="body1" fontWeight="bold">
          Role:
        </Typography>
      </Grid>
      <Grid size={{ xs: 8, sm: 8, md: 9, lg: 10, xl: 10 }}>
        {roles.map((item, index) => (
          <Chip
            key={index}
            label={item}
            sx={{
              textTransform: "capitalize",
              color: ROLE_COLORS[item],
              backgroundColor: alpha(ROLE_COLORS[item], 0.1)
            }}
          />
        ))}
      </Grid>
    </DeleteModalContainer>
  );
}
