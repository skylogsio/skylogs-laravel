import { ReactNode, useEffect } from "react";

import { zodResolver } from "@hookform/resolvers/zod";
import {
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
import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useForm } from "react-hook-form";
import { toast } from "react-toastify";
import { z } from "zod";

import type { IAlertRule, IAlertRuleCreateData } from "@/@types/alertRule";
import type { CreateUpdateModal } from "@/@types/global";
import { createAlertRule, updateAlertRule } from "@/api/alertRule";
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
    endpoints: z.array(z.string()).optional().default([]),
    accessUsers: z.array(z.string()).optional().default([])
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
  accessUsers: [],
  endpoints: [],
  enableAutoResolve: false,
  autoResolveMinutes: 0
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
      if (data.status) {
        toast.success("Client Api Alert Rule Created Successfully.");
        onSubmit();
        onClose?.();
      }
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

  function handleAutoResolve(event: React.ChangeEvent<HTMLInputElement>) {
    if (!event.target.checked) {
      clearErrors("autoResolveMinutes");
      setValue("autoResolveMinutes", 0);
    }
    setValue("enableAutoResolve", event.target.checked);
  }

  function handleSubmitForm(values: ClientAPIFormType) {
    if (data === "NEW") {
      createClientAPIMutation(values);
    } else if (data) {
      updateClientAPIMutation({ id: data.id, body: values });
    }
  }

  console.log("data:", data);

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
      reset({
        name: data.name,
        type: "api",
        accessUsers: data.user_ids,
        endpoints: data.endpoint_ids,
        enableAutoResolve: data.enableAutoResolve,
        autoResolveMinutes: data.autoResolveMinutes
      });
    }
  }, [reset, data]);

  return (
    <Stack component="form" onSubmit={handleSubmit(handleSubmitForm)} padding={2}>
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
        <Grid size={12} marginTop="auto" flex={1}></Grid>
      </Grid>
      <Stack direction="row" justifyContent="flex-end" spacing={2}>
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
