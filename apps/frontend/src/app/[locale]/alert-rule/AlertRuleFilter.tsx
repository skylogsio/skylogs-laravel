import { ReactNode, useState } from "react";

import {
  alpha,
  Autocomplete,
  Box,
  Chip,
  Grid2 as Grid,
  MenuItem,
  Stack,
  TextField,
  Typography,
  useTheme
} from "@mui/material";
import { useQuery } from "@tanstack/react-query";
import { FaCheck } from "react-icons/fa6";
import { IoNotifications, IoNotificationsOff } from "react-icons/io5";

import { getAlertFilterEndpointList } from "@/api/alertRule/alertRule";
import type { TableFilterComponentProps } from "@/components/Table/types";
import { ALERT_RULE_VARIANTS, type AlertRuleType } from "@/utils/alertRuleUtils";

type AlertRuleStatus = "resolved" | "warning" | "fire" | "";
type AlertRuleSilentStatus = "silent" | "not-silent" | "";

interface IAlertRuleFilters {
  alertname?: string;
  types?: Array<AlertRuleType>;
}

export default function AlertRuleFilter({ onChange }: TableFilterComponentProps) {
  const { palette } = useTheme();
  const [status, setStatus] = useState<AlertRuleStatus[]>([]);
  const [silentStatus, setSilentStatus] = useState<AlertRuleSilentStatus>("");

  const [filter, setFilter] = useState<IAlertRuleFilters>({});

  const { data: endpointList } = useQuery({
    queryKey: ["alert-rule-filter-endpoint-list"],
    queryFn: () => getAlertFilterEndpointList()
  });

  function handleChangeStatus(selectedStatus: AlertRuleStatus) {
    setStatus((prev) => {
      const temp = prev.includes(selectedStatus)
        ? prev.filter((item) => item !== selectedStatus)
        : [...prev, selectedStatus];
      onChange("status", temp);
      return temp;
    });
  }

  function handleChange(
    key: keyof IAlertRuleFilters,
    event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) {
    onChange(key, event.target.value);
    setFilter((prev) => ({ ...prev, [key]: event.target.value }));
  }

  function renderEndpointsChip(selectedEndpointIds: unknown): ReactNode {
    const selectedEndpoints = filter.types?.filter((item) =>
      (selectedEndpointIds as string[]).includes(item)
    );
    if (selectedEndpoints && selectedEndpoints.length > 0) {
      return (
        <Box sx={{ display: "flex", flexWrap: "wrap", gap: 0.5 }}>
          {selectedEndpoints.map((value, index) => (
            <Chip size="small" key={index} label={value} sx={{ textTransform: "capitalize" }} />
          ))}
        </Box>
      );
    }
    return <></>;
  }

  return (
    <Grid container spacing={1}>
      <Grid size={3}>
        <TextField
          size="small"
          label="Name"
          value={filter.alertname}
          variant="filled"
          onChange={(event) => handleChange("alertname", event)}
        />
      </Grid>
      <Grid size={3}>
        <TextField
          label="Type Of Data Source"
          variant="filled"
          select
          value={filter.types ?? []}
          slotProps={{ select: { multiple: true, renderValue: renderEndpointsChip } }}
          size="small"
          onChange={(event) => handleChange("types", event)}
        >
          {ALERT_RULE_VARIANTS.map((item) => (
            <MenuItem key={item.value} value={item.value} sx={{ textTransform: "capitalize" }}>
              <Stack direction="row" alignItems="center" spacing={1}>
                {item.icon}
                <Typography component="span">{item.value}</Typography>
              </Stack>
            </MenuItem>
          ))}
        </TextField>
      </Grid>
      <Grid size={6}>
        {/*fieldName:tags*/}
        <TextField size="small" label="Tags" variant="filled" />
      </Grid>
      <Grid size={3}>
        {/*fieldName:endpointId []*/}
        <Autocomplete
          multiple
          id="endpoints"
          size="small"
          options={endpointList ?? []}
          getOptionLabel={(option) => option.name}
          renderInput={(params) => (
            <TextField
              {...params}
              slotProps={{
                input: params.InputProps,
                inputLabel: params.InputLabelProps,
                htmlInput: params.inputProps
              }}
              variant="filled"
              label="Endpoints"
            />
          )}
        />
      </Grid>
      <Grid size={3}>
        {/*fieldName:silentStatus*/}
        <TextField
          label="Silent Status"
          variant="filled"
          select
          size="small"
          value={silentStatus}
          onChange={(event) => setSilentStatus(event.target.value as AlertRuleSilentStatus)}
        >
          <MenuItem value="">
            <Stack direction="row" alignItems="center" spacing={1}>
              <Typography component="span">All</Typography>
            </Stack>
          </MenuItem>
          <MenuItem value="silent">
            <Stack direction="row" alignItems="center" spacing={1}>
              <IoNotificationsOff color={palette.warning.main} size="1.4rem" />
              <Typography component="span">Silent</Typography>
            </Stack>
          </MenuItem>
          <MenuItem value="not-silent">
            <Stack direction="row" alignItems="center" spacing={1}>
              <IoNotifications color={palette.warning.main} size="1.4rem" />
              <Typography component="span">Not Silent</Typography>
            </Stack>
          </MenuItem>
        </TextField>
      </Grid>
      <Grid size={4}>
        {/*fieldName:status*/}
        <Stack direction="row" height="100%" alignItems="center" spacing={1}>
          <Typography variant="body2">Status: </Typography>
          <Chip
            icon={status.includes("resolved") ? <FaCheck /> : undefined}
            label="Resolved"
            onClick={() => handleChangeStatus("resolved")}
            variant="outlined"
            color="success"
            sx={({ palette }) => ({
              backgroundColor: alpha(palette.success.light, 0.1),
              borderColor: status.includes("resolved") ? palette.success.light : "transparent"
            })}
          />
          <Chip
            icon={status.includes("warning") ? <FaCheck /> : undefined}
            label="Warning"
            onClick={() => handleChangeStatus("warning")}
            variant="outlined"
            color="warning"
            sx={({ palette }) => ({
              backgroundColor: alpha(palette.warning.light, 0.1),
              borderColor: status.includes("warning") ? palette.warning.light : "transparent"
            })}
          />
          <Chip
            icon={status.includes("fire") ? <FaCheck /> : undefined}
            label="Fire"
            onClick={() => handleChangeStatus("fire")}
            variant="outlined"
            color="error"
            sx={({ palette }) => ({
              backgroundColor: alpha(palette.error.light, 0.1),
              borderColor: status.includes("fire") ? palette.error.light : "transparent"
            })}
          />
        </Stack>
      </Grid>
    </Grid>
  );
}
