import { ReactNode, useEffect } from "react";

import { zodResolver } from "@hookform/resolvers/zod";
import {
  Autocomplete,
  Box,
  Button,
  Chip,
  Grid2 as Grid,
  MenuItem,
  Stack,
  Switch,
  TextField,
  Typography
} from "@mui/material";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useForm } from "react-hook-form";
import { toast } from "react-toastify";
import { z } from "zod";

import type { IAlertRule, IAlertRuleCreateData } from "@/@types/alertRule";
import type { CreateUpdateModal } from "@/@types/global";
import { createAlertRule, getAlertRuleTags, updateAlertRule } from "@/api/alertRule/alertRule";
import type { ModalContainerProps } from "@/components/Modal/types";

const clientApiSchema = z
  .object({
    name: z
      .string({ required_error: "This field is Required." })
      .refine((data) => data.trim() !== "", {
        message: "This field is Required."
      }),
    type: z.literal("api").default("api"),
    enableAutoResolve: z.boolean().default(false),
    autoResolveMinutes: z.number({
      required_error: "This field is Required.",
      invalid_type_error: "This field is Required."
    }),
    endpointIds: z.array(z.string()).optional().default([]),
    userIds: z.array(z.string()).optional().default([]),
    tags: z.array(z.string()).optional().default([])
  })
  .superRefine((data, ctx) => {
    if (data.enableAutoResolve) {
      if (data.autoResolveMinutes === undefined) {
        ctx.addIssue({
          path: ["autoResolveMinutes"],
          message: "This field is Required.",
          code: "custom"
        });
      } else if (!Number.isInteger(data.autoResolveMinutes) || data.autoResolveMinutes <= 0) {
        ctx.addIssue({
          path: ["autoResolveMinutes"],
          message: "Must be a positive integer.",
          code: "custom"
        });
      }
    }
  });

type ClientAPIFormType = z.infer<typeof clientApiSchema>;
type ClientAPIModalProps = Pick<ModalContainerProps, "onClose"> & {
  data: CreateUpdateModal<IAlertRule>;
  onSubmit: () => void;
};

const defaultValues: ClientAPIFormType = {
  name: "",
  type: "api",
  userIds: [],
  endpointIds: [],
  enableAutoResolve: false,
  autoResolveMinutes: 0,
  tags: []
};

export default function ClientAPIForm({ onClose, onSubmit, data }: ClientAPIModalProps) {
  const queryClient = useQueryClient();
  const {
    register,
    handleSubmit,
    watch,
    setValue,
    reset,
    clearErrors,
    formState: { errors }
  } = useForm<ClientAPIFormType>({
    resolver: zodResolver(clientApiSchema),
    defaultValues
  });

  const requiredData = queryClient.getQueryData<IAlertRuleCreateData>(["alert-rule-create-data"]);

  const { mutate: createClientAPIMutation, isPending: isCreating } = useMutation({
    mutationFn: (body: ClientAPIFormType) => createAlertRule(body),
    onSuccess: (data) => {
      console.log(data);
      if (data.status) {
        toast.success("Client Api Alert Rule Created Successfully.");
        onSubmit();
        onClose?.();
      }
    },
    onError: (error) => {
      console.log(error);
    }
  });

  const { mutate: updateClientAPIMutation, isPending: isUpdating } = useMutation({
    mutationFn: ({ id, body }: { id: IAlertRule["id"]; body: ClientAPIFormType }) =>
      updateAlertRule(id, body),
    onSuccess: (data) => {
      if (data.status) {
        toast.success("Client Api Alert Rule Created Successfully.");
        onSubmit();
        onClose?.();
      }
    }
  });

  const { data: tagsList } = useQuery({
    queryKey: ["all-alert-rule-tags"],
    queryFn: () => getAlertRuleTags()
  });

  function handleAutoResolve(event: React.ChangeEvent<HTMLInputElement>) {
    if (!event.target.checked) {
      clearErrors("autoResolveMinutes");
      setValue("autoResolveMinutes", 0);
    }
    setValue("enableAutoResolve", event.target.checked);
  }

  function handleSubmitForm(values: ClientAPIFormType) {
    console.log(values);
    if (data === "NEW") {
      createClientAPIMutation(values);
    } else if (data) {
      updateClientAPIMutation({ id: data.id, body: values });
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
      reset(data as unknown as ClientAPIFormType);
    }
  }, [reset, data]);

  return (
    <Stack component="form" height="100%" onSubmit={handleSubmit(handleSubmitForm)} padding={2}>
      <Grid container spacing={2} flex={1} alignContent="flex-start">
        <Typography variant="h6" color="textPrimary" fontWeight="bold" component="div">
          {data === "NEW" ? "Create" : "Update"} Client API Alert
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
            id="endpoints"
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
            id="access-users"
            variant="filled"
            error={!!errors.userIds}
            helperText={errors.userIds?.message}
            {...register("userIds")}
            value={watch("userIds") ?? []}
            select
            slotProps={{
              select: {
                multiple: true,
                renderValue: renderUsersChip
              }
            }}
          >
            {requiredData?.users.map((user) => (
              <MenuItem key={user.id} value={user.id}>
                {user.name}
              </MenuItem>
            ))}
          </TextField>
        </Grid>
        <Grid size={6}>
          <Stack height="100%" direction="row" spacing={1} sx={{ alignItems: "center" }}>
            <Typography>Auto Resolve</Typography>
            <Switch checked={watch("enableAutoResolve")} onChange={handleAutoResolve} />
          </Stack>
        </Grid>
        <Grid size={6}>
          <TextField
            label="Auto Resolve After (Minutes)"
            variant="filled"
            type="number"
            disabled={!watch("enableAutoResolve")}
            error={!!errors.autoResolveMinutes}
            helperText={errors.autoResolveMinutes?.message}
            {...register("autoResolveMinutes", {
              valueAsNumber: true,
              setValueAs: (value) => parseInt(value)
            })}
          />
        </Grid>
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
      <Stack direction="row" justifyContent="flex-end" spacing={2} marginTop={2}>
        <Button disabled={isCreating || isUpdating} variant="outlined" onClick={onClose}>
          Cancel
        </Button>
        <Button disabled={isCreating || isUpdating} type="submit" variant="contained">
          {data === "NEW" ? "Create" : "Update"}
        </Button>
      </Stack>
    </Stack>
  );
}
