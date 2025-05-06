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
  ToggleButton,
  Typography
} from "@mui/material";
import { useMutation, useQueries, useQueryClient } from "@tanstack/react-query";
import { useFieldArray, useForm } from "react-hook-form";
import { HiPlus } from "react-icons/hi";
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
} from "@/api/alertRule/alertRule";
import ExtraField from "@/components/AlertRule/Forms/ExtraField";
import type { ModalContainerProps } from "@/components/Modal/types";
import ToggleButtonGroup from "@/components/ToggleButtonGroup";
import { type DataSourceType } from "@/utils/dataSourceUtils";
import { capitalizeFirstLetter } from "@/utils/general";

const QUERY_TYPE = ["dynamic", "textQuery"] as const;
const ALERT_RULE_TYPES = ["prometheus", "pmm"] as const;

const extraFieldSchema = z.object({
  key: z.string().refine((data) => data.trim() !== "", {
    message: "This field is Required."
  }),
  value: z.string().refine((data) => data.trim() !== "", {
    message: "This field is Required."
  })
});

const generalAlertRuleSchema = z.object({
  name: z
    .string({ required_error: "This field is Required." })
    .refine((data) => data.trim() !== "", {
      message: "This field is Required."
    }),
  type: z.enum(ALERT_RULE_TYPES),
  endpointIds: z.array(z.string()).optional().default([]),
  userIds: z.array(z.string()).optional().default([]),
  extraField: z.array(extraFieldSchema).optional().default([]),
  tags: z.array(z.string()).optional().default([]),
  dataSourceIds: z.array(z.string()).min(1, "Select at least one Data Source."),
  queryType: z.enum(QUERY_TYPE),
  dataSourceAlertName: z
    .string({ required_error: "This Field is Required." })
    .refine((data) => data.trim() !== "", {
      message: "This field is Required."
    })
});

type GeneralAlertRuleType = z.infer<typeof generalAlertRuleSchema>;
type GeneralAlertRuleModalProps = Pick<ModalContainerProps, "onClose"> & {
  data: CreateUpdateModal<IAlertRule>;
  onSubmit: () => void;
  type: DataSourceType;
};

const defaultKeyValue = { key: "", value: "" };

const defaultValues: GeneralAlertRuleType = {
  name: "",
  type: "prometheus",
  userIds: [],
  endpointIds: [],
  extraField: [],
  tags: [],
  dataSourceIds: [],
  dataSourceAlertName: "",
  queryType: "dynamic"
};

export default function GeneralAlertRuleForm({
  data,
  onSubmit,
  onClose,
  type
}: GeneralAlertRuleModalProps) {
  const queryClient = useQueryClient();
  const {
    register,
    handleSubmit,
    watch,
    setValue,
    reset,
    control,
    formState: { errors }
  } = useForm<GeneralAlertRuleType>({
    resolver: zodResolver(generalAlertRuleSchema),
    defaultValues
  });
  const {
    fields,
    append: appendNewKeyPair,
    remove: removeKeyPair
  } = useFieldArray({
    control,
    name: "extraField"
  });

  const requiredData = queryClient.getQueryData<IAlertRuleCreateData>(["alert-rule-create-data"]);

  const [{ data: tagsList }, { data: alertRuleNameList }, { data: dataSourceList }] = useQueries({
    queries: [
      {
        queryKey: ["all-alert-rule-tags"],
        queryFn: () => getAlertRuleTags()
      },
      {
        queryKey: ["all-alert-rule-names", type],
        queryFn: () => getDataSourceAlertName(type)
      },
      {
        queryKey: ["alert-rule-data-source", type],
        queryFn: () => getAlertRuleDataSourcesByAlertType(type)
      }
    ]
  });

  const { mutate: createPrometheusMutation, isPending: isCreating } = useMutation({
    mutationFn: (body: GeneralAlertRuleType) => createAlertRule(body),
    onSuccess: (data) => {
      if (data.status) {
        toast.success(`${capitalizeFirstLetter(type)} Alert Rule Created Successfully.`);
        onSubmit();
        onClose?.();
      }
    },
    onError: (error) => {
      console.log(error);
    }
  });

  const { mutate: updatePrometheusMutation, isPending: isUpdating } = useMutation({
    mutationFn: ({ id, body }: { id: IAlertRule["id"]; body: GeneralAlertRuleType }) =>
      updateAlertRule(id, body),
    onSuccess: (data) => {
      if (data.status) {
        toast.success(`${capitalizeFirstLetter(type)} Alert Rule Updated Successfully.`);
        onSubmit();
        onClose?.();
      }
    }
  });

  function handleSubmitForm(values: GeneralAlertRuleType) {
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

  function renderDataSourceChip(selectedDataSourceIds: unknown): ReactNode {
    const selectedDataSources = dataSourceList?.filter((dataSource) =>
      (selectedDataSourceIds as string[]).includes(dataSource.id)
    );
    const selectedDataSourceNames = selectedDataSources?.map((dataSource) => dataSource.name) ?? [];
    return (
      <Box sx={{ display: "flex", flexWrap: "wrap", gap: 0.5 }}>
        {selectedDataSourceNames.map((value, index) => (
          <Chip size="small" key={index} label={value} />
        ))}
      </Box>
    );
  }

  useEffect(() => {
    if (data === "NEW") {
      reset(defaultValues);
    } else if (data) {
      reset(data as unknown as GeneralAlertRuleType);
    }
  }, [reset, data]);

  useEffect(() => {
    if (type === "prometheus" || type === "pmm") {
      setValue("type", type);
    }
  }, [setValue, type]);

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
          {data === "NEW" ? "Create" : "Update"} {type} Alert
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
        <Grid size={12} display="flex" justifyContent="center">
          <ToggleButtonGroup
            exclusive
            value={watch("queryType")}
            onChange={(_, value) => value !== null && setValue("queryType", value)}
          >
            {QUERY_TYPE.map((value) => (
              <ToggleButton
                key={value}
                disabled={value === "textQuery"}
                value={value}
                sx={{ textTransform: "capitalize !important" }}
              >
                {value}
              </ToggleButton>
            ))}
          </ToggleButtonGroup>
        </Grid>
        {watch("queryType") === "dynamic" ? (
          <Grid container size={12}>
            <Grid size={6}>
              <TextField
                label="Data Source"
                variant="filled"
                error={!!errors.dataSourceIds}
                helperText={errors.dataSourceIds?.message}
                {...register("dataSourceIds")}
                value={watch("dataSourceIds") ?? []}
                select
                slotProps={{ select: { multiple: true, renderValue: renderDataSourceChip } }}
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
                freeSolo={type === "pmm"}
                value={watch("dataSourceAlertName")}
                onChange={(_, value) => setValue("dataSourceAlertName", value ?? "")}
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
            {fields.map((field, index) => (
              <ExtraField
                key={field.id}
                keyTextFieldProps={{
                  value: watch(`extraField.${index}.key`),
                  onChange: (value) => setValue(`extraField.${index}.key`, value ?? ""),
                  error: !!errors.extraField?.[index]?.key,
                  helperText: errors.extraField?.[index]?.key?.message
                }}
                valueTextFieldProps={{
                  value: watch(`extraField.${index}.value`),
                  onChange: (value) => setValue(`extraField.${index}.value`, value ?? ""),
                  error: !!errors.extraField?.[index]?.value,
                  helperText: errors.extraField?.[index]?.value?.message
                }}
                onDelete={() => removeKeyPair(index)}
              />
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
      <Stack direction="row" justifyContent="flex-end" spacing={2} paddingY={2}>
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
