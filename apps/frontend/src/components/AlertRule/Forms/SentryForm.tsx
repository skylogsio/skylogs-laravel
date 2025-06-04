import { type ReactNode, useEffect } from "react";

import { zodResolver } from "@hookform/resolvers/zod";
import {
  Autocomplete,
  Box,
  Button,
  Chip,
  Grid2 as Grid,
  MenuItem,
  Stack,
  TextField,
  Typography
} from "@mui/material";
import { useMutation, useQueries, useQueryClient } from "@tanstack/react-query";
import { useForm } from "react-hook-form";
import { toast } from "react-toastify";
import { z } from "zod";

import type { IAlertRule, IAlertRuleCreateData } from "@/@types/alertRule";
import type { CreateUpdateModal } from "@/@types/global";
import {
  createAlertRule,
  getAlertRuleDataSourcesByAlertType,
  getAlertRuleTags,
  getDataSourceAlertName,
  updateAlertRule
} from "@/api/alertRule";
import type { ModalContainerProps } from "@/components/Modal/types";

const sentryAlertRuleSchema = z.object({
  name: z
    .string({ required_error: "This field is Required." })
    .refine((data) => data.trim() !== "", {
      message: "This field is Required."
    }),
  type: z.literal("sentry"),
  endpointIds: z.array(z.string()).optional().default([]),
  userIds: z.array(z.string()).optional().default([]),
  tags: z.array(z.string()).optional().default([]),
  dataSourceId: z
    .string({ required_error: "This field is Required." })
    .refine((data) => data.trim() !== "", {
      message: "This field is Required."
    }),
  dataSourceAlertName: z
    .string({ required_error: "This Field is Required." })
    .refine((data) => data.trim() !== "", {
      message: "This field is Required."
    })
});

type SentryAlertRuleType = z.infer<typeof sentryAlertRuleSchema>;
type SentryAlertRuleModalProps = Pick<ModalContainerProps, "onClose"> & {
  data: CreateUpdateModal<IAlertRule>;
  onSubmit: () => void;
};

const defaultValues: SentryAlertRuleType = {
  name: "",
  type: "sentry",
  userIds: [],
  endpointIds: [],
  tags: [],
  dataSourceId: "",
  dataSourceAlertName: ""
};

export default function SentryAlertRuleForm({
  data,
  onSubmit,
  onClose
}: SentryAlertRuleModalProps) {
  const queryClient = useQueryClient();
  const {
    register,
    handleSubmit,
    watch,
    setValue,
    reset,
    formState: { errors }
  } = useForm<SentryAlertRuleType>({
    resolver: zodResolver(sentryAlertRuleSchema),
    defaultValues
  });

  const requiredData = queryClient.getQueryData<IAlertRuleCreateData>(["alert-rule-create-data"]);

  const [{ data: tagsList }, { data: alertRuleNameList }, { data: dataSourceList }] = useQueries({
    queries: [
      {
        queryKey: ["all-alert-rule-tags"],
        queryFn: () => getAlertRuleTags()
      },
      {
        queryKey: ["all-alert-rule-names", "sentry"],
        queryFn: () => getDataSourceAlertName("sentry")
      },
      {
        queryKey: ["alert-rule-data-source", "sentry"],
        queryFn: () => getAlertRuleDataSourcesByAlertType("sentry")
      }
    ]
  });

  const { mutate: createPrometheusMutation, isPending: isCreating } = useMutation({
    mutationFn: (body: SentryAlertRuleType) => createAlertRule(body),
    onSuccess: (data) => {
      if (data.status) {
        toast.success("Sentry Alert Rule Created Successfully.");
        onSubmit();
        onClose?.();
      }
    },
    onError: (error) => {
      console.log(error);
    }
  });

  const { mutate: updatePrometheusMutation, isPending: isUpdating } = useMutation({
    mutationFn: ({ id, body }: { id: IAlertRule["id"]; body: SentryAlertRuleType }) =>
      updateAlertRule(id, body),
    onSuccess: (data) => {
      if (data.status) {
        toast.success("Sentry Alert Rule Updated Successfully.");
        onSubmit();
        onClose?.();
      }
    }
  });

  function handleSubmitForm(values: SentryAlertRuleType) {
    if (data === "NEW") {
      createPrometheusMutation(values);
    } else if (data) {
      updatePrometheusMutation({ id: data.id, body: values });
    }
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

  useEffect(() => {
    if (data === "NEW") {
      reset(defaultValues);
    } else if (data) {
      reset(data as unknown as SentryAlertRuleType);
    }
  }, [reset, data]);

  return (
    <Stack
      component="form"
      height="100%"
      onSubmit={handleSubmit(handleSubmitForm, (error) => console.log(error))}
      padding={2}
      flex={1}
    >
      <Grid container spacing={2} flex={1} alignContent="flex-start">
        <Typography
          variant="h6"
          color="textPrimary"
          textTransform="capitalize"
          fontWeight="bold"
          component="div"
        >
          {data === "NEW" ? "Create" : "Update"} Alert
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
            error={!!errors.endpointIds}
            helperText={errors.endpointIds?.message}
            {...register("endpointIds")}
            value={watch("endpointIds") ?? []}
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
            error={!!errors.userIds}
            helperText={errors.userIds?.message}
            {...register("userIds")}
            value={watch("userIds") ?? []}
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
        <Grid size={6}>
          <TextField
            label="Data Source"
            variant="filled"
            error={!!errors.dataSourceId}
            helperText={errors.dataSourceId?.message}
            {...register("dataSourceId")}
            value={watch("dataSourceId") ?? []}
            select
          >
            {dataSourceList?.map((dataSource) => (
              <MenuItem key={dataSource.id} value={dataSource.id}>
                {dataSource.name}
              </MenuItem>
            ))}
          </TextField>
        </Grid>
        <Grid size={6}>
          <Autocomplete
            id="data-source-alert-rule-name"
            options={alertRuleNameList ?? []}
            freeSolo
            value={watch("dataSourceAlertName")}
            onChange={(_, value) => setValue("dataSourceAlertName", value ?? "")}
            autoSelect
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
                error={!!errors.dataSourceAlertName}
                helperText={errors.dataSourceAlertName?.message}
                variant="filled"
                label="DataSource Alert Name"
              />
            )}
          />
        </Grid>
        <Grid size={12}>
          <Autocomplete
            multiple
            id="alert-tags"
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
      <Stack direction="row" justifyContent="flex-end" spacing={2} paddingTop={2}>
        <Button variant="outlined" disabled={isCreating || isUpdating} onClick={onClose}>
          Cancel
        </Button>
        <Button type="submit" variant="contained" disabled={isCreating || isUpdating}>
          {data === "NEW" ? "Create" : "Update"}
        </Button>
      </Stack>
    </Stack>
  );
}
