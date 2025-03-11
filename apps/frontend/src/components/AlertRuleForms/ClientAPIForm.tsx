import {
  Button,
  Grid2 as Grid,
  Stack,
  Switch,
  TextField,
  Typography
} from "@mui/material";

export default function ClientAPIForm() {
  return (
    <Grid container spacing={2} padding={2} flex={1} alignContent="flex-start" minHeight='100%'>
      <Typography variant="h6" color="textPrimary" fontWeight="bold" component="div">
        Create Client API Alert
      </Typography>
      <Grid size={12}>
        <TextField label="Name" variant="filled" />
      </Grid>
      <Grid size={6}>
        <TextField label="Endpoints" variant="filled" select />
      </Grid>
      <Grid size={6}>
        <TextField label="Users" variant="filled" select />
      </Grid>
      <Grid size={6}>
        <Stack height="100%" direction="row" spacing={1} sx={{ alignItems: "center" }}>
          <Typography>Auto Resolve</Typography>
          <Switch defaultChecked />
        </Stack>
      </Grid>
      <Grid size={6}>
        <TextField label="Minutes" variant="filled" />
      </Grid>
      <Grid size={12} marginTop="auto">
        <Stack direction="row" justifyContent="flex-end" spacing={2}>
          <Button variant="outlined">Cancel</Button>
          <Button variant="contained">Create</Button>
        </Stack>
      </Grid>
    </Grid>
  );
}
