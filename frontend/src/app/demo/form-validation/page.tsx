'use client';

import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useForm, FormProvider } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { 
  FormInput, 
  FormTextarea, 
  FormSelect, 
  FormCheckbox, 
  FormNumberInput,
  FormDateInput,
  FormErrorSummary 
} from '@/components/form/form-components';
import { 
  loginSchema, 
  registerSchema, 
  profileBasicInfoSchema,
  bookingSchema,
  type LoginFormData,
  type RegisterFormData,
  type ProfileBasicInfoFormData,
  type BookingFormData,
} from '@/lib/validation-schemas';
import { CheckCircle2, Shield, Zap, AlertCircle } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';

export default function FormValidationDemo() {
  const { toast } = useToast();

  // Login Form
  const loginMethods = useForm<LoginFormData>({
    resolver: zodResolver(loginSchema),
    defaultValues: { email: '', password: '' },
  });

  // Register Form
  const registerMethods = useForm<RegisterFormData>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      name: '',
      email: '',
      password: '',
      passwordConfirmation: '',
    },
  });

  // Profile Form
  const profileMethods = useForm<ProfileBasicInfoFormData>({
    resolver: zodResolver(profileBasicInfoSchema),
    defaultValues: {
      name: 'John Doe',
      email: 'john@example.com',
      phone: '+1234567890',
      bio: '',
    },
  });

  // Booking Form
  const bookingMethods = useForm<BookingFormData>({
    resolver: zodResolver(bookingSchema),
    defaultValues: {
      propertyId: 1,
      checkIn: '',
      checkOut: '',
      guests: 1,
      specialRequests: '',
    },
  });

  const handleFormSubmit = (formName: string) => (data: any) => {
    toast({
      title: '✅ Form validated successfully!',
      description: `${formName} data is valid. Check console for details.`,
    });
    console.log(`${formName} data:`, data);
  };

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-12 max-w-7xl">
        {/* Header */}
        <div className="text-center mb-12">
          <Badge className="mb-4">React Hook Form + Zod</Badge>
          <h1 className="text-4xl font-bold mb-4">Form Validation System</h1>
          <p className="text-xl text-muted-foreground max-w-2xl mx-auto">
            Type-safe form validation with real-time error feedback and comprehensive schema validation
          </p>
        </div>

        {/* Features */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <CheckCircle2 className="h-5 w-5 text-green-500" />
              Key Features
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div className="flex items-start gap-3">
                <Shield className="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="font-semibold">Type-Safe Validation</p>
                  <p className="text-sm text-muted-foreground">Zod schemas with TypeScript inference</p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <Zap className="h-5 w-5 text-yellow-500 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="font-semibold">Real-Time Feedback</p>
                  <p className="text-sm text-muted-foreground">Instant validation as user types</p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <AlertCircle className="h-5 w-5 text-red-500 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="font-semibold">Error Handling</p>
                  <p className="text-sm text-muted-foreground">Clear, actionable error messages</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Form Examples */}
        <Tabs defaultValue="login" className="space-y-4">
          <TabsList className="grid w-full grid-cols-4">
            <TabsTrigger value="login">Login</TabsTrigger>
            <TabsTrigger value="register">Register</TabsTrigger>
            <TabsTrigger value="profile">Profile</TabsTrigger>
            <TabsTrigger value="booking">Booking</TabsTrigger>
          </TabsList>

          {/* Login Form */}
          <TabsContent value="login">
            <Card>
              <CardHeader>
                <CardTitle>Login Form Validation</CardTitle>
                <CardDescription>
                  Email and password validation with custom error messages
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                  {/* Form */}
                  <div>
                    <FormProvider {...loginMethods}>
                      <form 
                        onSubmit={loginMethods.handleSubmit(handleFormSubmit('Login'))}
                        className="space-y-4"
                      >
                        <FormErrorSummary />
                        
                        <FormInput
                          name="email"
                          label="Email"
                          type="email"
                          placeholder="john@example.com"
                          required
                        />

                        <FormInput
                          name="password"
                          label="Password"
                          type="password"
                          placeholder="••••••••"
                          required
                          description="Minimum 8 characters"
                        />

                        <Button type="submit" className="w-full">
                          Validate Login
                        </Button>
                      </form>
                    </FormProvider>
                  </div>

                  {/* Validation Rules */}
                  <div className="bg-muted p-4 rounded-lg">
                    <h4 className="font-semibold mb-3">Validation Rules:</h4>
                    <ul className="space-y-2 text-sm">
                      <li className="flex items-start gap-2">
                        <CheckCircle2 className="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                        <span><strong>Email:</strong> Valid email format required</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle2 className="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                        <span><strong>Password:</strong> Minimum 8 characters</span>
                      </li>
                    </ul>

                    <div className="mt-4 p-3 bg-background rounded border">
                      <p className="text-xs font-mono">
                        Try: test@example.com / password
                      </p>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Register Form */}
          <TabsContent value="register">
            <Card>
              <CardHeader>
                <CardTitle>Register Form Validation</CardTitle>
                <CardDescription>
                  Complex password rules and password confirmation matching
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                  {/* Form */}
                  <div>
                    <FormProvider {...registerMethods}>
                      <form 
                        onSubmit={registerMethods.handleSubmit(handleFormSubmit('Register'))}
                        className="space-y-4"
                      >
                        <FormErrorSummary />
                        
                        <FormInput
                          name="name"
                          label="Full Name"
                          placeholder="John Doe"
                          required
                        />

                        <FormInput
                          name="email"
                          label="Email"
                          type="email"
                          placeholder="john@example.com"
                          required
                        />

                        <FormInput
                          name="password"
                          label="Password"
                          type="password"
                          placeholder="••••••••"
                          required
                        />

                        <FormInput
                          name="passwordConfirmation"
                          label="Confirm Password"
                          type="password"
                          placeholder="••••••••"
                          required
                        />

                        <Button type="submit" className="w-full">
                          Validate Registration
                        </Button>
                      </form>
                    </FormProvider>
                  </div>

                  {/* Validation Rules */}
                  <div className="bg-muted p-4 rounded-lg">
                    <h4 className="font-semibold mb-3">Password Requirements:</h4>
                    <ul className="space-y-2 text-sm">
                      <li className="flex items-start gap-2">
                        <CheckCircle2 className="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                        <span>Minimum 8 characters</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle2 className="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                        <span>At least one uppercase letter</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle2 className="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                        <span>At least one lowercase letter</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle2 className="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                        <span>At least one number</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle2 className="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                        <span>Passwords must match</span>
                      </li>
                    </ul>

                    <div className="mt-4 p-3 bg-background rounded border">
                      <p className="text-xs font-mono">
                        Valid: Password123
                      </p>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Profile Form */}
          <TabsContent value="profile">
            <Card>
              <CardHeader>
                <CardTitle>Profile Form Validation</CardTitle>
                <CardDescription>
                  Optional fields, phone number format, and character limits
                </CardDescription>
              </CardHeader>
              <CardContent>
                <FormProvider {...profileMethods}>
                  <form 
                    onSubmit={profileMethods.handleSubmit(handleFormSubmit('Profile'))}
                    className="space-y-4"
                  >
                    <FormErrorSummary />
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <FormInput
                        name="name"
                        label="Full Name"
                        placeholder="John Doe"
                        required
                      />

                      <FormInput
                        name="email"
                        label="Email"
                        type="email"
                        placeholder="john@example.com"
                        required
                      />
                    </div>

                    <FormInput
                      name="phone"
                      label="Phone Number"
                      type="tel"
                      placeholder="+1234567890"
                      description="International format (optional)"
                    />

                    <FormTextarea
                      name="bio"
                      label="Bio"
                      placeholder="Tell us about yourself..."
                      description="Maximum 500 characters"
                      rows={4}
                    />

                    <Button type="submit">
                      Update Profile
                    </Button>
                  </form>
                </FormProvider>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Booking Form */}
          <TabsContent value="booking">
            <Card>
              <CardHeader>
                <CardTitle>Booking Form Validation</CardTitle>
                <CardDescription>
                  Date validation, number ranges, and cross-field validation
                </CardDescription>
              </CardHeader>
              <CardContent>
                <FormProvider {...bookingMethods}>
                  <form 
                    onSubmit={bookingMethods.handleSubmit(handleFormSubmit('Booking'))}
                    className="space-y-4"
                  >
                    <FormErrorSummary />
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <FormDateInput
                        name="checkIn"
                        label="Check-in Date"
                        required
                      />

                      <FormDateInput
                        name="checkOut"
                        label="Check-out Date"
                        required
                        description="Must be after check-in"
                      />
                    </div>

                    <FormNumberInput
                      name="guests"
                      label="Number of Guests"
                      min={1}
                      max={50}
                      required
                    />

                    <FormTextarea
                      name="specialRequests"
                      label="Special Requests"
                      placeholder="Any special requirements?"
                      description="Optional, max 500 characters"
                      rows={3}
                    />

                    <Button type="submit">
                      Validate Booking
                    </Button>
                  </form>
                </FormProvider>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>

        {/* Code Example */}
        <Card className="mt-8">
          <CardHeader>
            <CardTitle>Implementation Example</CardTitle>
            <CardDescription>How to use validation in your forms</CardDescription>
          </CardHeader>
          <CardContent>
            <pre className="bg-muted p-4 rounded-lg overflow-x-auto text-sm">
{`import { useForm, FormProvider } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { loginSchema, type LoginFormData } from '@/lib/validation-schemas';
import { FormInput, FormErrorSummary } from '@/components/form/form-components';

export function LoginForm() {
  const methods = useForm<LoginFormData>({
    resolver: zodResolver(loginSchema),
    defaultValues: { email: '', password: '' },
  });

  const onSubmit = async (data: LoginFormData) => {
    // data is type-safe and validated!
    await login(data.email, data.password);
  };

  return (
    <FormProvider {...methods}>
      <form onSubmit={methods.handleSubmit(onSubmit)}>
        <FormErrorSummary />
        
        <FormInput
          name="email"
          label="Email"
          type="email"
          required
        />

        <FormInput
          name="password"
          label="Password"
          type="password"
          required
        />

        <Button type="submit">Sign In</Button>
      </form>
    </FormProvider>
  );
}`}
            </pre>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}
