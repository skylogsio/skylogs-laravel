import { type ReactNode, useState } from "react";

import {
  Autocomplete,
  Box,
  Checkbox,
  Chip,
  FormControlLabel,
  Grid2 as Grid,
  MenuItem,
  Stack,
  TextField,
  Typography,
  useTheme
} from "@mui/material";
import { useQueries } from "@tanstack/react-query";
import { IoNotifications, IoNotificationsOff } from "react-icons/io5";

import { getAlertFilterEndpointList, getAlertRuleTags } from "@/api/alertRule";
import type { TableFilterComponentProps } from "@/components/Table/types";
import { ALERT_RULE_VARIANTS, type AlertRuleType } from "@/utils/alertRuleUtils";

type AlertRuleSilentStatus = "silent" | "not-silent" | "";

interface IAlertRuleFilters {
  alertname?: string;
  status?: string;
  types?: Array<AlertRuleType>;
  endpointId?: string | string[];
  tags?: string | string[];
}

export default function AlertRuleFilter({ onChange }: TableFilterComponentProps) {
  const { palette } = useTheme();
  const [silentStatus, setSilentStatus] = useState<AlertRuleSilentStatus>("");

  const [filter, setFilter] = useState<IAlertRuleFilters>({});

  const [{ data: tagsList }, { data: endpointList }] = useQueries({
    queries: [
      {
        queryKey: ["all-alert-rule-tags"],
        queryFn: () => getAlertRuleTags()
      },
      {
        queryKey: ["alert-rule-filter-endpoint-list"],
        queryFn: () => getAlertFilterEndpointList()
      }
    ]
  });

  function handleChange(
    key: keyof IAlertRuleFilters,
    event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement> | string | string[]
  ) {
    if (typeof event === "string" || Array.isArray(event)) {
      onChange(key, event);
      setFilter((prev) => ({ ...prev, [key]: event }));
    } else {
      onChange(key, event.target.value);
      setFilter((prev) => ({ ...prev, [key]: event.target.value }));
    }
  }

  function handleSilentFilter(event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) {
    onChange("silentStatus", event.target.value);
    setSilentStatus(event.target.value as AlertRuleSilentStatus);
  }

  function renderAlertRuleList() {
    return Object.entries(ALERT_RULE_VARIANTS).map(([key, value]) => (
      <MenuItem key={key} value={key}>
        <Stack direction="row" alignItems="center" spacing={1}>
          <value.Icon size={value.defaultSize} color={value.defaultColor} />
          <Typography component="span">{value.label}</Typography>
        </Stack>
      </MenuItem>
    ));
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
          {renderAlertRuleList()}
        </TextField>
      </Grid>

      <Grid size={3}>
        <Autocomplete
          multiple
          id="endpoints-filter"
          size="small"
          options={endpointList ?? []}
          getOptionLabel={(option) => option.name}
          onChange={(_, value) =>
            handleChange(
              "endpointId",
              value.map((item) => item.id)
            )
          }
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
        <TextField
          label="Silent Status"
          variant="filled"
          select
          size="small"
          value={silentStatus}
          onChange={handleSilentFilter}
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
          <MenuItem value="unsilent">
            <Stack direction="row" alignItems="center" spacing={1}>
              <IoNotifications color={palette.warning.main} size="1.4rem" />
              <Typography component="span">Not Silent</Typography>
            </Stack>
          </MenuItem>
        </TextField>
      </Grid>
      <Grid size={9}>
        <Autocomplete
          multiple
          id="alert-tags-filter"
          size="small"
          options={tagsList ?? []}
          freeSolo
          onChange={(_, value) => handleChange("tags", value)}
          renderTags={(value: readonly string[], getItemProps) =>
            value.map((option: string, index: number) => {
              const { key, ...itemProps } = getItemProps({ index });
              return <Chip variant="filled" label={option} key={key} {...itemProps} />;
            })
          }
          renderInput={(params) => (
            <TextField
              {...params}
              slotProps={{
                input: params.InputProps,
                inputLabel: params.InputLabelProps,
                htmlInput: params.inputProps
              }}
              variant="filled"
              label="Tags"
            />
          )}
        />
      </Grid>
      <Grid size={3}>
        <FormControlLabel
          sx={{ margin: 0 }}
          label="Only Show Fired Alerts"
          control={
            <Checkbox
              onChange={(_, checked) => handleChange("status", checked ? "critical" : "")}
            />
          }
        />
      </Grid>
    </Grid>
  );
}
