import { Grid2 as Grid, Typography } from "@mui/material";

import type { IEndpoint } from "@/@types/endpoint";
import DeleteModalContainer from "@/components/DeleteModal/DeleteModalContainer";
import type { DeleteModalProps } from "@/components/DeleteModal/DeleteModalTypes";
import { renderEndPointChip } from "@/utils/endpointVariants";

export default function DeleteEndPointModal({
  data,
  ...props
}: DeleteModalProps & { data: IEndpoint }) {
  const { name, value, type, chatId } = data;
  return (
    <DeleteModalContainer {...props}>
      <Grid size={2}>
        <Typography variant="body1" fontWeight="bold">
          Name:
        </Typography>
      </Grid>
      <Grid size={10}>
        <Typography variant="body1" fontWeight="normal">
          {name}
        </Typography>
      </Grid>
      <Grid size={2}>
        <Typography variant="body1" fontWeight="bold">
          Type:
        </Typography>
      </Grid>
      <Grid size={10}>
        <Typography variant="body1" fontWeight="normal">
          {renderEndPointChip(type, "small")}
        </Typography>
      </Grid>
      <Grid size={2}>
        <Typography variant="body1" fontWeight="bold">
          Value:
        </Typography>
      </Grid>
      <Grid size={10}>
        <Typography variant="body1" fontWeight="normal">
          {type === "telegram" ? chatId : value}
        </Typography>
      </Grid>
    </DeleteModalContainer>
  );
}
