import { type ReactNode, useState } from "react";

import { zodResolver } from "@hookform/resolvers/zod";
import {
  Autocomplete,
  Box,
  Button,
  Chip,
  Grid2 as Grid,
  IconButton,
  MenuItem,
  Stack,
  TextField,
  ToggleButton,
  Typography
} from "@mui/material";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { useFieldArray, useForm } from "react-hook-form";
import { HiPlus, HiX } from "react-icons/hi";
import { z } from "zod";

import type { IAlertRule, IAlertRuleCreateData } from "@/@types/alertRule";
import type { CreateUpdateModal } from "@/@types/global";
import { getAlertRuleTags } from "@/api/alertRule";
import type { ModalContainerProps } from "@/components/Modal/types";
import ToggleButtonGroup from "@/components/ToggleButtonGroup";

const prometheusKeyValueSchema = z.object({
  key: z.string().refine((data) => data.trim() !== "", {
    message: "This field is Required."
  }),
  value: z.string().refine((data) => data.trim() !== "", {
    message: "This field is Required."
  })
});

const prometheusSchema = z.object({
  name: z
    .string({ required_error: "This field is Required." })
    .refine((data) => data.trim() !== "", {
      message: "This field is Required."
    }),
  type: z.literal("api").default("api"),
  endpoints: z.array(z.string()).optional().default([]),
  accessUsers: z.array(z.string()).optional().default([]),
  keyPairs: z.array(prometheusKeyValueSchema).optional().default([]),
  tags: z.array(z.string()).optional().default([])
});

type PrometheusType = z.infer<typeof prometheusSchema>;
type PrometheusModalProps = Pick<ModalContainerProps, "onClose"> & {
  data: CreateUpdateModal<IAlertRule>;
  onSubmit: () => void;
};

const defaultKeyValue = { key: "", value: "" };

const defaultValues: PrometheusType = {
  name: "",
  type: "api",
  accessUsers: [],
  endpoints: [],
  keyPairs: [defaultKeyValue],
  tags: []
};

export default function PrometheusForm({ data }: PrometheusModalProps) {
  const queryClient = useQueryClient();
  const {
    register,
    handleSubmit,
    watch,
    setValue,
    control,
    formState: { errors }
  } = useForm<PrometheusType>({
    resolver: zodResolver(prometheusSchema),
    defaultValues
  });
  const {
    fields,
    append: appendNewKeyPair,
    remove: removeKeyPair
  } = useFieldArray({
    control,
    name: "keyPairs"
  });

  const [mode, setMode] = useState<"dynamic-inputs" | "text-box">("dynamic-inputs");

  const requiredData = queryClient.getQueryData<IAlertRuleCreateData>(["alert-rule-create-data"]);

  const { data: tagsList } = useQuery({
    queryKey: ["all-alert-rule-tags"],
    queryFn: () => getAlertRuleTags()
  });

  function handleSubmitForm(values: PrometheusType) {
    console.log(values);
  }

  function renderEndpointsChip(selectedEndpointIds: unknown): ReactNode {
    const selectedEndpoints = requiredData?.endpoints.filter((item) =>
      (selectedEndpointIds as string[]).includes(item.id)
    );
    if (selectedEndpoints && selectedEndpoints.length > 0) {
      return (
        <Box sx={{ display: "flex", flexWrap: "wrap", gap: 0.5 }}>
          {selectedEndpoints.map((value) => (
            <Chip size="small" key={value.id} label={value.name} />
          ))}
        </Box>
      );
    }
    return <></>;
  }

  function renderUsersChip(selectedUserIds: unknown): ReactNode {
    const selectedUsers = requiredData?.users.filter((item) =>
      (selectedUserIds as string[]).includes(item.id)
    );
    if (selectedUsers && selectedUsers.length > 0) {
      return (
        <Box sx={{ display: "flex", flexWrap: "wrap", gap: 0.5 }}>
          {selectedUsers.map((value) => (
            <Chip size="small" key={value.id} label={value.name} />
          ))}
        </Box>
      );
    }
    return <></>;
  }

  return (
    <Stack component="form" onSubmit={handleSubmit(handleSubmitForm)} padding={2} flex={1}>
      <Grid container spacing={2} flex={1} alignContent="flex-start">
        <Typography variant="h6" color="textPrimary" fontWeight="bold" component="div">
          {data === "NEW" ? "Create" : "Update"} Prometheus Alert
        </Typography>
        <Grid size={12}>
          <TextField
            label="Name"
            variant="filled"
            error={!!errors.name}
            helperText={errors.name?.message}
            {...register("name")}
          />
        </Grid>
        <Grid size={6}>
          <TextField
            label="Endpoints"
            variant="filled"
            error={!!errors.endpoints}
            helperText={errors.endpoints?.message}
            {...register("endpoints")}
            value={watch("endpoints") ?? []}
            slotProps={{
              select: {
                multiple: true,
                renderValue: renderEndpointsChip
              }
            }}
            select
          >
            {requiredData?.endpoints.map((endpoint) => (
              <MenuItem key={endpoint.id} value={endpoint.id}>
                {endpoint.name}
              </MenuItem>
            ))}
          </TextField>
        </Grid>
        <Grid size={6}>
          <TextField
            label="Users"
            variant="filled"
            error={!!errors.accessUsers}
            helperText={errors.accessUsers?.message}
            {...register("accessUsers")}
            value={watch("accessUsers") ?? []}
            slotProps={{
              select: {
                multiple: true,
                renderValue: renderUsersChip
              }
            }}
            select
          >
            {requiredData?.users.map((user) => (
              <MenuItem key={user.id} value={user.id}>
                {user.name}
              </MenuItem>
            ))}
          </TextField>
        </Grid>
        <Grid size={12} display="flex" justifyContent="center">
          <ToggleButtonGroup exclusive value={mode} onChange={(_, value) => setMode(value)}>
            <ToggleButton value="dynamic-inputs">Dynamic Inputs</ToggleButton>
            <ToggleButton value="text-box">Text Box</ToggleButton>
          </ToggleButtonGroup>
        </Grid>
        {mode === "dynamic-inputs" ? (
          <Grid container size={12}>
            <Grid size={6}>
              <TextField
                label="Data Source"
                variant="filled"
                select
                slotProps={{ select: { multiple: true } }}
              />
            </Grid>
            <Grid size={6}>
              <TextField label="Alert Name" variant="filled" select />
            </Grid>
            {fields.map((field, index) => (
              <Grid container size={12} key={field.id}>
                <Grid size={6}>
                  <TextField
                    label="Key"
                    variant="filled"
                    {...register(`keyPairs.${index}.key`)}
                    error={!!errors.keyPairs?.[index]?.key}
                    helperText={errors.keyPairs?.[index]?.key?.message}
                  />
                </Grid>
                <Grid size={6}>
                  <Stack direction="row" alignItems="center" spacing={1}>
                    <TextField
                      label="Value"
                      variant="filled"
                      {...register(`keyPairs.${index}.value`)}
                      error={!!errors.keyPairs?.[index]?.value}
                      helperText={errors.keyPairs?.[index]?.value?.message}
                    />
                    {index > 0 && (
                      <IconButton color="error" onClick={() => removeKeyPair(index)}>
                        <HiX />
                      </IconButton>
                    )}
                  </Stack>
                </Grid>
              </Grid>
            ))}
            <Button
              startIcon={<HiPlus />}
              variant="outlined"
              fullWidth
              onClick={() => appendNewKeyPair(defaultKeyValue)}
            >
              Add New Key Value
            </Button>
          </Grid>
        ) : (
          <Grid size={12}>
            <TextField label="Query" variant="filled" multiline minRows={4} />
          </Grid>
        )}

        <Grid size={12}>
          <Autocomplete
            multiple
            id="api-alert-tags"
            options={tagsList ?? []}
            freeSolo
            value={watch("tags")}
            onChange={(_, value) => setValue("tags", value)}
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
      </Grid>
    </Stack>
  );
}
