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
import { type AxiosResponse } from "axios";
import { useForm } from "react-hook-form";
import { toast } from "react-toastify";
import { z } from "zod";

import type { IAlertRuleCreateData } from "@/@types/alertRule";
import type { CreateUpdateModal, ServerResponse } from "@/@types/global";
import { createAlertRule } from "@/api/alertRule";
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
    autoResolveMinutes: z.number().min(1, "The value should be greater than 0.").or(z.literal("")),
    endpoints: z.array(z.string()).min(1, "This field is Required."),
    accessUsers: z.array(z.string()).min(1, "This field is Required.")
  })
  .refine(
    (data) =>
      !data.enableAutoResolve ||
      (data.enableAutoResolve && (data.autoResolveMinutes !== "" || +data.autoResolveMinutes > 0)),
    { path: ["autoResolveMinutes"], message: "This field is Required." }
  );

type ClientAPIFormType = z.infer<typeof clientApiSchema>;
type ClientAPIModalProps = Pick<ModalContainerProps, "onClose"> & {
  data: CreateUpdateModal<unknown>;
  onSubmit: () => void;
};

const defaultValues: ClientAPIFormType = {
  name: "",
  type: "api",
  accessUsers: [],
  endpoints: [],
  enableAutoResolve: false,
  autoResolveMinutes: ""
};

export default function ClientAPIForm({ onClose, onSubmit }: ClientAPIModalProps) {
  const queryClient = useQueryClient();
  const {
    register,
    handleSubmit,
    watch,
    setValue,
    reset,
    formState: { errors }
  } = useForm<ClientAPIFormType>({
    resolver: zodResolver(clientApiSchema),
    defaultValues
  });

  const data = queryClient.getQueryData<AxiosResponse<IAlertRuleCreateData>>([
    "alert-rule-create-data"
  ]);

  const { mutate: createClientAPIMutation, isPending: isCreating } = useMutation({
    mutationFn: (body: ClientAPIFormType) => createAlertRule(body),
    onSuccess: ({ data }: ServerResponse<unknown>) => {
      if (data.status) {
        toast.success("Client Api Alert Rule Created Successfully.");
        onSubmit();
        onClose?.();
      }
    }
  });

  function handleAutoResolve(event: React.ChangeEvent<HTMLInputElement>) {
    if (!event.target.checked) {
      setValue("autoResolveMinutes", "");
    }
    setValue("enableAutoResolve", event.target.checked);
  }

  function handleSubmitForm(values: ClientAPIFormType) {
    createClientAPIMutation(values);
  }

  function renderEndpointsChip(selectedEndpointIds: unknown): ReactNode {
    const selectedEndpoints = data?.data.endpoints.filter((item) =>
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
    const selectedUsers = data?.data.users.filter((item) =>
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
    reset(defaultValues);
  }, [reset]);

  return (
    <Stack component="form" onSubmit={handleSubmit(handleSubmitForm)} padding={2}>
      <Grid container spacing={2} flex={1} alignContent="flex-start">
        <Typography variant="h6" color="textPrimary" fontWeight="bold" component="div">
          Create Client API Alert
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
            {data?.data?.endpoints.map((endpoint) => (
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
            {data?.data?.users.map((user) => (
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
            label="Minutes"
            variant="filled"
            type="number"
            disabled={!watch("enableAutoResolve")}
            error={!!errors.autoResolveMinutes}
            helperText={errors.autoResolveMinutes?.message}
            {...register("autoResolveMinutes", { valueAsNumber: true })}
          />
        </Grid>
        <Grid size={12} marginTop="auto" flex={1}></Grid>
      </Grid>
      <Stack direction="row" justifyContent="flex-end" spacing={2}>
        <Button disabled={isCreating} variant="outlined" onClick={onClose}>
          Cancel
        </Button>
        <Button disabled={isCreating} type="submit" variant="contained">
          Create
        </Button>
      </Stack>
    </Stack>
  );
}
