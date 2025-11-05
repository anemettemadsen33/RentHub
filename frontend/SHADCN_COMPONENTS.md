# shadcn/ui Components Documentation

This project now includes all shadcn/ui components. Below is a comprehensive guide on the available components and how to use them.

## Available Components (57 total)

### Layout & Structure
- **Card** - Container with header, content, and footer sections
- **Separator** - Visual divider between content
- **Tabs** - Tabbed interface for organizing content
- **Accordion** - Expandable/collapsible content sections
- **Collapsible** - Single collapsible content section
- **Resizable** - Resizable panels for layouts
- **Scroll Area** - Custom scrollable container

### Navigation
- **Navigation Menu** - Main navigation component
- **Breadcrumb** - Hierarchical page navigation
- **Pagination** - Page navigation controls
- **Menubar** - Application-style menu bar

### Buttons & Actions
- **Button** - Primary action button
- **Button Group** - Grouped buttons
- **Toggle** - Binary toggle button
- **Toggle Group** - Grouped toggle buttons

### Forms & Inputs
- **Input** - Text input field
- **Textarea** - Multi-line text input
- **Input Group** - Grouped input components
- **Input OTP** - One-time password input
- **Label** - Form field label
- **Checkbox** - Checkbox input
- **Radio Group** - Radio button group
- **Select** - Dropdown select
- **Switch** - Toggle switch
- **Slider** - Range slider input
- **Field** - Form field wrapper
- **Form** - Form with validation support

### Overlays & Dialogs
- **Dialog** - Modal dialog
- **Alert Dialog** - Alert/confirmation dialog
- **Sheet** - Slide-out panel
- **Drawer** - Mobile-friendly drawer
- **Popover** - Floating popover content
- **Hover Card** - Content shown on hover
- **Tooltip** - Hover tooltip
- **Context Menu** - Right-click context menu
- **Dropdown Menu** - Dropdown menu

### Feedback & Status
- **Alert** - Alert/notification banner
- **Sonner** - Toast notifications (recommended over deprecated toast)
- **Toast** - Toast notifications (deprecated, use Sonner instead)
- **Progress** - Progress bar
- **Spinner** - Loading spinner
- **Skeleton** - Content loading placeholder
- **Badge** - Status badge

### Data Display
- **Avatar** - User avatar
- **Table** - Data table
- **Chart** - Data visualization charts
- **Calendar** - Date picker/calendar
- **Carousel** - Image/content carousel
- **Empty** - Empty state component
- **Aspect Ratio** - Maintain aspect ratio container
- **Kbd** - Keyboard key display

### Utility
- **Command** - Command palette (⌘K style)

## Usage Examples

### Basic Button
```tsx
import { Button } from "@/components/ui/button"

export function Example() {
  return <Button>Click me</Button>
}
```

### Form with Input and Label
```tsx
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"

export function LoginForm() {
  return (
    <div className="space-y-2">
      <Label htmlFor="email">Email</Label>
      <Input id="email" type="email" placeholder="Enter your email" />
    </div>
  )
}
```

### Dialog
```tsx
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog"
import { Button } from "@/components/ui/button"

export function DialogExample() {
  return (
    <Dialog>
      <DialogTrigger asChild>
        <Button>Open Dialog</Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Are you sure?</DialogTitle>
          <DialogDescription>
            This action cannot be undone.
          </DialogDescription>
        </DialogHeader>
      </DialogContent>
    </Dialog>
  )
}
```

### Card
```tsx
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"

export function CardExample() {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Property Details</CardTitle>
        <CardDescription>View property information</CardDescription>
      </CardHeader>
      <CardContent>
        <p>Content goes here</p>
      </CardContent>
    </Card>
  )
}
```

### Toast Notifications (Sonner)
```tsx
import { Button } from "@/components/ui/button"
import { toast } from "sonner"

export function ToastExample() {
  return (
    <Button onClick={() => toast.success("Booking confirmed!")}>
      Show Toast
    </Button>
  )
}
```

### Checkbox with Label
```tsx
import { Checkbox } from "@/components/ui/checkbox"
import { Label } from "@/components/ui/label"

export function CheckboxExample() {
  return (
    <div className="flex items-center space-x-2">
      <Checkbox id="terms" />
      <Label htmlFor="terms">Accept terms and conditions</Label>
    </div>
  )
}
```

### Select Dropdown
```tsx
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"

export function SelectExample() {
  return (
    <Select>
      <SelectTrigger className="w-[180px]">
        <SelectValue placeholder="Select a property type" />
      </SelectTrigger>
      <SelectContent>
        <SelectItem value="apartment">Apartment</SelectItem>
        <SelectItem value="house">House</SelectItem>
        <SelectItem value="condo">Condo</SelectItem>
      </SelectContent>
    </Select>
  )
}
```

### Calendar
```tsx
import { Calendar } from "@/components/ui/calendar"
import { useState } from "react"

export function CalendarExample() {
  const [date, setDate] = useState<Date | undefined>(new Date())

  return (
    <Calendar
      mode="single"
      selected={date}
      onSelect={setDate}
      className="rounded-md border"
    />
  )
}
```

### Table
```tsx
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"

export function TableExample() {
  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Property</TableHead>
          <TableHead>Location</TableHead>
          <TableHead>Price</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow>
          <TableCell>Modern Apartment</TableCell>
          <TableCell>New York</TableCell>
          <TableCell>$2,500/mo</TableCell>
        </TableRow>
      </TableBody>
    </Table>
  )
}
```

### Tabs
```tsx
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"

export function TabsExample() {
  return (
    <Tabs defaultValue="overview">
      <TabsList>
        <TabsTrigger value="overview">Overview</TabsTrigger>
        <TabsTrigger value="details">Details</TabsTrigger>
        <TabsTrigger value="reviews">Reviews</TabsTrigger>
      </TabsList>
      <TabsContent value="overview">Overview content</TabsContent>
      <TabsContent value="details">Details content</TabsContent>
      <TabsContent value="reviews">Reviews content</TabsContent>
    </Tabs>
  )
}
```

### Command Palette
```tsx
import {
  Command,
  CommandDialog,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from "@/components/ui/command"

export function CommandExample() {
  return (
    <Command>
      <CommandInput placeholder="Search properties..." />
      <CommandList>
        <CommandEmpty>No results found.</CommandEmpty>
        <CommandGroup heading="Suggestions">
          <CommandItem>Search by location</CommandItem>
          <CommandItem>Search by price</CommandItem>
        </CommandGroup>
      </CommandList>
    </Command>
  )
}
```

## Additional Resources

- [shadcn/ui Official Documentation](https://ui.shadcn.com)
- [Radix UI Documentation](https://www.radix-ui.com/docs/primitives/overview/introduction)
- Component source code: `src/components/ui/`

## Notes

- All components are built on top of Radix UI primitives
- Components are fully customizable with Tailwind CSS
- TypeScript types are included for all components
- Components follow accessibility best practices
- The `sonner` component is recommended over the deprecated `toast` component
