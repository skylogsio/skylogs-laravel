import { useState } from "react";

import { alpha, Chip, Grid2 as Grid, MenuItem, Stack, TextField, Typography } from "@mui/material";
import { FaCheck } from "react-icons/fa6";

import type { TableFilterComponentProps } from "@/components/Table/types";
import { ALERT_RULE_VARIANTS, type AlertRuleType } from "@/utils/alertRuleUtils";

type AlertRuleStatus = "running" | "warning" | "fire" | "";
type AlertRuleSilentStatus = "silent" | "not-silent" | "";

interface IAlertRuleFilters {
  alertname?: string;
  types?: Array<AlertRuleType>;
}

export default function AlertRuleFilter({ onChange }: TableFilterComponentProps) {
  const [status, setStatus] = useState<AlertRuleStatus>("");
  const [silentStatus, setSilentStatus] = useState<AlertRuleSilentStatus>("");

  const [filter, setFilter] = useState<IAlertRuleFilters>({});

  function handleChangeStatus(selectedStatus: AlertRuleStatus) {
    setStatus((prev) => (prev === selectedStatus ? "" : selectedStatus));
  }

  function handleChange(
    key: keyof IAlertRuleFilters,
    event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) {
    onChange(key, event.target.value);
    setFilter((prev) => ({ ...prev, [key]: event.target.value }));
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
          slotProps={{ select: { multiple: true } }}
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
        <TextField size="small" label="Tags" variant="filled" />
      </Grid>
      <Grid size={3}>
        <TextField size="small" label="Notify" variant="filled" select></TextField>
      </Grid>
      <Grid size={3}>
        <TextField
          label="Silent Status"
          variant="filled"
          select
          size="small"
          value={silentStatus}
          onChange={(event) => setSilentStatus(event.target.value as AlertRuleSilentStatus)}
        >
          <MenuItem value="">All</MenuItem>
          <MenuItem value="silent">Silent</MenuItem>
          <MenuItem value="not-silent">Not Silent</MenuItem>
        </TextField>
      </Grid>
      <Grid size={4}>
        <Stack direction="row" height="100%" alignItems="center" spacing={1}>
          <Typography variant="body2">Status: </Typography>
          <Chip
            icon={status === "running" ? <FaCheck /> : undefined}
            label="Running"
            onClick={() => handleChangeStatus("running")}
            variant="outlined"
            color="success"
            sx={({ palette }) => ({
              backgroundColor: alpha(palette.success.light, 0.1),
              borderColor: status === "running" ? palette.success.light : "transparent"
            })}
          />
          <Chip
            icon={status === "warning" ? <FaCheck /> : undefined}
            label="Warning"
            onClick={() => handleChangeStatus("warning")}
            variant="outlined"
            color="warning"
            sx={({ palette }) => ({
              backgroundColor: alpha(palette.warning.light, 0.1),
              borderColor: status === "warning" ? palette.warning.light : "transparent"
            })}
          />
          <Chip
            icon={status === "fire" ? <FaCheck /> : undefined}
            label="Fire"
            onClick={() => handleChangeStatus("fire")}
            variant="outlined"
            color="error"
            sx={({ palette }) => ({
              backgroundColor: alpha(palette.error.light, 0.1),
              borderColor: status === "fire" ? palette.error.light : "transparent"
            })}
          />
        </Stack>
      </Grid>
    </Grid>
  );
}
