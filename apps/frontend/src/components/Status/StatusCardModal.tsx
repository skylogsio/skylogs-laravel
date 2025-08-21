import { zodResolver } from "@hookform/resolvers/zod";
import { Autocomplete, Button, Chip, Grid2 as Grid, TextField } from "@mui/material";
import { useMutation, useQuery } from "@tanstack/react-query";
import { useForm } from "react-hook-form";
import { toast } from "react-toastify";
import { z } from "zod";

import { CreateUpdateModal } from "@/@types/global";
import { IStatusCard } from "@/@types/status";
import { getAlertRuleTags } from "@/api/alertRule";
import { createStatusCard, udpateStatusCard } from "@/api/status";

import ModalContainer from "../Modal";
import { ModalContainerProps } from "../Modal/types";

const schema = z.object({
  name: z
    .string({ required_error: "This field is Required." })
    .refine((data) => data.trim() !== "", {
      message: "This field is Required."
    }),
  tags: z.array(z.string()).min(1, "This field is Required.")
});

type StatusCardType = z.infer<typeof schema>;
type StatusCardModalProps = Pick<ModalContainerProps, "onClose" | "open"> & {
  data: CreateUpdateModal<IStatusCard>;
  onSubmit: () => void;
};

const defaultValues: StatusCardType = {
  name: "",
  tags: []
};

export default function StatusCardModal({ data, open, onSubmit, onClose }: StatusCardModalProps) {
  const {
    register,
    handleSubmit,
    watch,
    setValue,
    formState: { errors }
  } = useForm<StatusCardType>({
    resolver: zodResolver(schema),
    defaultValues
  });

  const { data: tagsList } = useQuery({
    queryKey: ["all-alert-rule-tags"],
    queryFn: () => getAlertRuleTags()
  });

  const { mutate: createStatusCardMutation, isPending: isCreating } = useMutation({
    mutationFn: (body: StatusCardType) => createStatusCard(body),
    onSuccess: (data) => {
      if (data.status) {
        toast.success("Status Card Created Successfully.");
        onSubmit();
        onClose?.();
      }
    },
  });

  const { mutate: updateStatusCardMutation, isPending: isUpdating } = useMutation({
    mutationFn: ({ id, body }: { id: IStatusCard["id"]; body: StatusCardType }) =>
      udpateStatusCard(id, body),
    onSuccess: (data) => {
      if (data.status) {
        toast.success("Status Card Created Successfully.");
        onSubmit();
        onClose?.();
      }
    }
  });

  function handleSubmitForm(values: StatusCardType) {
    if (data === "NEW") {
      createStatusCardMutation(values);
    } else if (data) {
      updateStatusCardMutation({ id: data.id, body: values });
    }
  }

  return (
    <ModalContainer
      title={`${data === "NEW" ? "Create New" : "Update"} Status`}
      open={open}
      onClose={onClose}
      disableEscapeKeyDown
    >
      <Grid
        component="form"
        onSubmit={handleSubmit(handleSubmitForm, (error) => console.log(error))}
        container
        spacing={2}
        width="100%"
        display="flex"
        marginTop="2rem"
      >
        <Grid size={12}>
          <TextField
            label="Name"
            variant="filled"
            error={!!errors.name}
            helperText={errors.name?.message}
            {...register("name")}
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
        <Grid size={12} marginTop="1rem">
          <Button
            disabled={isCreating || isUpdating}
            type="submit"
            variant="contained"
            size="large"
            fullWidth
          >
            {data === "NEW" ? "Create" : "Update"}
          </Button>
        </Grid>
      </Grid>
    </ModalContainer>
  );
}
