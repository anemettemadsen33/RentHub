/**
 * Reusable Form Components with React Hook Form + Zod
 * 
 * Type-safe form components that integrate with react-hook-form
 * Includes automatic error display and validation
 */

'use client';

import * as React from 'react';
import { useFormContext, Controller } from 'react-hook-form';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { cn } from '@/lib/utils';

// ============================================================================
// FORM FIELD WRAPPER
// ============================================================================

interface FormFieldProps {
  name: string;
  label?: string;
  description?: string;
  required?: boolean;
  children: React.ReactNode;
  className?: string;
}

export function FormField({ 
  name, 
  label, 
  description, 
  required, 
  children, 
  className 
}: FormFieldProps) {
  const { formState: { errors } } = useFormContext();
  const error = errors[name];

  return (
    <div className={cn('space-y-2', className)}>
      {label && (
        <Label htmlFor={name} className="flex items-center gap-1">
          {label}
          {required && <span className="text-destructive">*</span>}
        </Label>
      )}
      {children}
      {description && !error && (
        <p className="text-sm text-muted-foreground">{description}</p>
      )}
      {error && (
        <p className="text-sm font-medium text-destructive" role="alert">
          {error.message as string}
        </p>
      )}
    </div>
  );
}

// ============================================================================
// FORM INPUT
// ============================================================================

interface FormInputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  name: string;
  label?: string;
  description?: string;
}

export function FormInput({ 
  name, 
  label, 
  description, 
  required,
  className,
  ...props 
}: FormInputProps) {
  const { register, formState: { errors } } = useFormContext();
  const error = errors[name];

  return (
    <FormField name={name} label={label} description={description} required={required}>
      <Input
        id={name}
        {...register(name)}
        className={cn(error && 'border-destructive focus-visible:ring-destructive', className)}
        aria-invalid={error ? 'true' : 'false'}
        aria-describedby={error ? `${name}-error` : undefined}
        suppressHydrationWarning
        {...props}
      />
    </FormField>
  );
}

// ============================================================================
// FORM TEXTAREA
// ============================================================================

interface FormTextareaProps extends React.TextareaHTMLAttributes<HTMLTextAreaElement> {
  name: string;
  label?: string;
  description?: string;
}

export function FormTextarea({ 
  name, 
  label, 
  description, 
  required,
  className,
  ...props 
}: FormTextareaProps) {
  const { register, formState: { errors } } = useFormContext();
  const error = errors[name];

  return (
    <FormField name={name} label={label} description={description} required={required}>
      <Textarea
        id={name}
        {...register(name)}
        className={cn(error && 'border-destructive focus-visible:ring-destructive', className)}
        aria-invalid={error ? 'true' : 'false'}
        suppressHydrationWarning
        {...props}
      />
    </FormField>
  );
}

// ============================================================================
// FORM SELECT
// ============================================================================

interface FormSelectProps {
  name: string;
  label?: string;
  description?: string;
  required?: boolean;
  placeholder?: string;
  options: { value: string; label: string }[];
  className?: string;
}

export function FormSelect({ 
  name, 
  label, 
  description, 
  required,
  placeholder = 'Select an option',
  options,
  className,
}: FormSelectProps) {
  const { control, formState: { errors } } = useFormContext();
  const error = errors[name];

  return (
    <FormField name={name} label={label} description={description} required={required} className={className}>
      <Controller
        name={name}
        control={control}
        render={({ field }) => (
          <Select onValueChange={field.onChange} defaultValue={field.value}>
            <SelectTrigger 
              className={cn(error && 'border-destructive focus:ring-destructive')}
              aria-invalid={error ? 'true' : 'false'}
            >
              <SelectValue placeholder={placeholder} />
            </SelectTrigger>
            <SelectContent>
              {options.map((option) => (
                <SelectItem key={option.value} value={option.value}>
                  {option.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        )}
      />
    </FormField>
  );
}

// ============================================================================
// FORM CHECKBOX
// ============================================================================

interface FormCheckboxProps {
  name: string;
  label?: string;
  description?: string;
  className?: string;
}

export function FormCheckbox({ 
  name, 
  label, 
  description,
  className,
}: FormCheckboxProps) {
  const { control, formState: { errors } } = useFormContext();
  const error = errors[name];

  return (
    <div className={cn('space-y-2', className)}>
      <Controller
        name={name}
        control={control}
        render={({ field }) => (
          <div className="flex items-start space-x-3">
            <Checkbox
              id={name}
              checked={field.value}
              onCheckedChange={field.onChange}
              aria-invalid={error ? 'true' : 'false'}
            />
            <div className="space-y-1 leading-none">
              {label && (
                <Label 
                  htmlFor={name}
                  className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                >
                  {label}
                </Label>
              )}
              {description && (
                <p className="text-sm text-muted-foreground">{description}</p>
              )}
            </div>
          </div>
        )}
      />
      {error && (
        <p className="text-sm font-medium text-destructive" role="alert">
          {error.message as string}
        </p>
      )}
    </div>
  );
}

// ============================================================================
// FORM NUMBER INPUT
// ============================================================================

interface FormNumberInputProps extends Omit<React.InputHTMLAttributes<HTMLInputElement>, 'type'> {
  name: string;
  label?: string;
  description?: string;
  min?: number;
  max?: number;
  step?: number;
}

export function FormNumberInput({ 
  name, 
  label, 
  description, 
  required,
  min,
  max,
  step = 1,
  className,
  ...props 
}: FormNumberInputProps) {
  const { register, formState: { errors } } = useFormContext();
  const error = errors[name];

  return (
    <FormField name={name} label={label} description={description} required={required}>
      <Input
        id={name}
        type="number"
        {...register(name, { 
          valueAsNumber: true,
          min,
          max,
        })}
        min={min}
        max={max}
        step={step}
        className={cn(error && 'border-destructive focus-visible:ring-destructive', className)}
        aria-invalid={error ? 'true' : 'false'}
        {...props}
      />
    </FormField>
  );
}

// ============================================================================
// FORM DATE INPUT
// ============================================================================

interface FormDateInputProps extends Omit<React.InputHTMLAttributes<HTMLInputElement>, 'type'> {
  name: string;
  label?: string;
  description?: string;
}

export function FormDateInput({ 
  name, 
  label, 
  description, 
  required,
  className,
  ...props 
}: FormDateInputProps) {
  const { register, formState: { errors } } = useFormContext();
  const error = errors[name];

  return (
    <FormField name={name} label={label} description={description} required={required}>
      <Input
        id={name}
        type="date"
        {...register(name)}
        className={cn(error && 'border-destructive focus-visible:ring-destructive', className)}
        aria-invalid={error ? 'true' : 'false'}
        {...props}
      />
    </FormField>
  );
}

// ============================================================================
// FORM ERROR SUMMARY
// ============================================================================

export function FormErrorSummary() {
  const { formState: { errors } } = useFormContext();
  const errorMessages = Object.entries(errors)
    .map(([name, error]) => ({
      name,
      message: error?.message as string,
    }))
    .filter(error => error.message);

  if (errorMessages.length === 0) return null;

  return (
    <div className="rounded-lg bg-destructive/10 p-4 border border-destructive/20" role="alert">
      <h3 className="font-semibold text-destructive mb-2">
        Please fix the following errors:
      </h3>
      <ul className="list-disc list-inside space-y-1">
        {errorMessages.map((error, index) => (
          <li key={index} className="text-sm text-destructive">
            {error.message}
          </li>
        ))}
      </ul>
    </div>
  );
}
