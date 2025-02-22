import { useEffect, useState } from "react";

import { zodResolver } from "@hookform/resolvers/zod";
import { Button, Checkbox, FormControlLabel, Grid2 as Grid, TextField } from "@mui/material";
import { useMutation } from "@tanstack/react-query";
import { useForm } from "react-hook-form";
import { toast } from "react-toastify";
import * as z from "zod";

import { changePassword } from "@/api/user";
import ModalContainer from "@/components/Modal";
import { ModalContainerProps } from "@/components/Modal/types";

const changePasswordSchema = z
  .object({
    //TODO: Add more validation for password
    password: z
      .string({ required_error: "This field is Required." })
      .refine((data) => data.trim() !== "", {
        message: "This field is Required."
      }),
    newPassword: z
      .string({ required_error: "This field is Required." })
      .refine((data) => data.trim() !== "", {
        message: "This field is Required."
      }),
    confirmNewPassword: z
      .string({ required_error: "This field is Required." })
      .refine((data) => data.trim() !== "", {
        message: "This field is Required."
      })
  })
  .refine((data) => data.newPassword === data.confirmNewPassword, {
    message: "Confirm Password does not match.",
    path: ["confirmPassword"]
  });

type ChangePasswordFormType = z.infer<typeof changePasswordSchema>;

const defaultValues: ChangePasswordFormType = {
  password: "",
  newPassword: "",
  confirmNewPassword: ""
};

export default function ChangePasswordModal({
  open,
  onClose,
  userId
}: Pick<ModalContainerProps, "open" | "onClose"> & { userId: string }) {
  const {
    register,
    handleSubmit,
    reset,
    formState: { errors }
  } = useForm<ChangePasswordFormType>({
    resolver: zodResolver(changePasswordSchema),
    defaultValues
  });
  const [showPassword, setShowPassword] = useState(false);

  const { mutate: changePasswordMutation, isPending: isCreating } = useMutation({
    mutationFn: (body: ChangePasswordFormType) => changePassword(userId, body),
    onSuccess: () => {
      toast.success("Password Changed Successfully.");
      onClose?.();
    }
  });

  function handleTogglePassword(_: unknown, checked: boolean) {
    setShowPassword(checked);
  }

  function handleSubmitForm(data: ChangePasswordFormType) {
    changePasswordMutation(data);
  }

  useEffect(() => {
    reset(defaultValues);
  }, [reset, open]);

  return (
    <ModalContainer title="Change User Password" open={open} onClose={onClose} disableEscapeKeyDown>
      <Grid
        component="form"
        onSubmit={handleSubmit(handleSubmitForm)}
        container
        spacing={2}
        width="100%"
        display="flex"
        marginTop="2rem"
      >
        <Grid size={6}>
          <TextField
            label="Password"
            type={showPassword ? "text" : "password"}
            variant="filled"
            {...register("password")}
            error={!!errors.password}
            helperText={errors.password?.message}
          />
        </Grid>
        <Grid size={6}>
          <TextField
            label="New Password"
            type={showPassword ? "text" : "password"}
            variant="filled"
            {...register("newPassword")}
            error={!!errors.newPassword}
            helperText={errors.newPassword?.message}
          />
        </Grid>
        <Grid size={6}>
          <TextField
            label="Confirm New Password"
            type={showPassword ? "text" : "password"}
            variant="filled"
            {...register("confirmNewPassword")}
            error={!!errors.confirmNewPassword}
            helperText={errors.confirmNewPassword?.message}
          />
        </Grid>
        <Grid size={6} display="flex">
          <FormControlLabel
            sx={{ userSelect: "none" }}
            control={
              <Checkbox id="ShowPassword" checked={showPassword} onChange={handleTogglePassword} />
            }
            label="Show Password"
          />
        </Grid>
        <Grid size={12} marginTop="1rem">
          <Button type="submit" variant="contained" size="large" fullWidth disabled={isCreating}>
            Change Password
          </Button>
        </Grid>
      </Grid>
    </ModalContainer>
  );
}
