# shadcn/ui Components Reference

## ✅ Instalate și Configurate

### Layout & Structure
- ✅ **Card** - Container pentru conținut grupat
- ✅ **Separator** - Linie de separare
- ✅ **Tabs** - Navigare cu tab-uri
- ✅ **Sheet** - Panel lateral slide-in
- ✅ **Drawer** - Bottom drawer pentru mobile
- ✅ **Accordion** - Conținut collapsible
- ✅ **Collapsible** - Secțiuni expandable
- ✅ **Resizable** - Panouri redimensionabile
- ✅ **Scroll Area** - Zone cu scroll customizat
- ✅ **Aspect Ratio** - Containere cu aspect ratio fix

### Forms & Input
- ✅ **Input** - Text input field
- ✅ **Textarea** - Multi-line text input
- ✅ **Select** - Dropdown select
- ✅ **Checkbox** - Checkbox input
- ✅ **Switch** - Toggle switch
- ✅ **Radio Group** - Radio button group
- ✅ **Form** - Form wrapper cu validare
- ✅ **Label** - Input labels
- ✅ **Button** - Butoane cu variante
- ✅ **Input OTP** - One-time password input

### Navigation
- ✅ **Navigation Menu** - Mega menu
- ✅ **Breadcrumb** - Breadcrumb navigation
- ✅ **Menubar** - Application menu bar
- ✅ **Command** - Command palette (⌘K)
- ✅ **Pagination** - Page navigation
- ✅ **Context Menu** - Right-click menu

### Feedback & Overlays
- ✅ **Alert** - Static alerts
- ✅ **Alert Dialog** - Modal confirmations
- ✅ **Dialog** - Modal dialogs
- ✅ **Toast** (Sonner) - Notifications
- ✅ **Progress** - Progress bars
- ✅ **Skeleton** - Loading placeholders
- ✅ **Tooltip** - Hover tooltips
- ✅ **Hover Card** - Rich hover content

### Data Display
- ✅ **Table** - Data tables
- ✅ **Badge** - Status badges
- ✅ **Avatar** - User avatars
- ✅ **Carousel** - Image/content carousel

### Utility
- ✅ **Toggle** - Toggle button
- ✅ **Toggle Group** - Toggle button groups

---

## 📖 Usage Examples

### Card
\`\`\`tsx
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';

<Card>
  <CardHeader>
    <CardTitle>Title</CardTitle>
    <CardDescription>Description</CardDescription>
  </CardHeader>
  <CardContent>
    Content here
  </CardContent>
</Card>
\`\`\`

### Button
\`\`\`tsx
import { Button } from '@/components/ui/button';

<Button>Default</Button>
<Button variant="secondary">Secondary</Button>
<Button variant="destructive">Delete</Button>
<Button variant="outline">Outline</Button>
<Button variant="ghost">Ghost</Button>
<Button size="sm">Small</Button>
<Button size="lg">Large</Button>
\`\`\`

### Input + Label
\`\`\`tsx
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

<div>
  <Label htmlFor="email">Email</Label>
  <Input id="email" type="email" placeholder="Email" />
</div>
\`\`\`

### Select
\`\`\`tsx
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

<Select>
  <SelectTrigger>
    <SelectValue placeholder="Select option" />
  </SelectTrigger>
  <SelectContent>
    <SelectItem value="1">Option 1</SelectItem>
    <SelectItem value="2">Option 2</SelectItem>
  </SelectContent>
</Select>
\`\`\`

### Tabs
\`\`\`tsx
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

<Tabs defaultValue="tab1">
  <TabsList>
    <TabsTrigger value="tab1">Tab 1</TabsTrigger>
    <TabsTrigger value="tab2">Tab 2</TabsTrigger>
  </TabsList>
  <TabsContent value="tab1">Content 1</TabsContent>
  <TabsContent value="tab2">Content 2</TabsContent>
</Tabs>
\`\`\`

### Toast (Sonner)
\`\`\`tsx
import { toast } from 'sonner';

// Success
toast.success('Saved successfully');

// Error
toast.error('Failed to save', {
  description: 'Please try again'
});

// Info
toast.info('New update available');

// Warning
toast.warning('Please verify your email');

// Loading
const toastId = toast.loading('Saving...');
// Later...
toast.success('Saved!', { id: toastId });
\`\`\`

### Dialog
\`\`\`tsx
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';

<Dialog>
  <DialogTrigger asChild>
    <Button>Open Dialog</Button>
  </DialogTrigger>
  <DialogContent>
    <DialogHeader>
      <DialogTitle>Title</DialogTitle>
    </DialogHeader>
    <p>Dialog content</p>
  </DialogContent>
</Dialog>
\`\`\`

### Table
\`\`\`tsx
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

<Table>
  <TableHeader>
    <TableRow>
      <TableHead>Name</TableHead>
      <TableHead>Status</TableHead>
    </TableRow>
  </TableHeader>
  <TableBody>
    <TableRow>
      <TableCell>John Doe</TableCell>
      <TableCell>Active</TableCell>
    </TableRow>
  </TableBody>
</Table>
\`\`\`

### Badge
\`\`\`tsx
import { Badge } from '@/components/ui/badge';

<Badge>Default</Badge>
<Badge variant="secondary">Secondary</Badge>
<Badge variant="destructive">Error</Badge>
<Badge variant="outline">Outline</Badge>
\`\`\`

### Switch
\`\`\`tsx
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';

<div className="flex items-center space-x-2">
  <Switch id="airplane-mode" />
  <Label htmlFor="airplane-mode">Airplane Mode</Label>
</div>
\`\`\`

### Carousel
\`\`\`tsx
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/ui/carousel';

<Carousel>
  <CarouselContent>
    <CarouselItem>Item 1</CarouselItem>
    <CarouselItem>Item 2</CarouselItem>
    <CarouselItem>Item 3</CarouselItem>
  </CarouselContent>
  <CarouselPrevious />
  <CarouselNext />
</Carousel>
\`\`\`

### Avatar
\`\`\`tsx
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';

<Avatar>
  <AvatarImage src="https://github.com/shadcn.png" />
  <AvatarFallback>CN</AvatarFallback>
</Avatar>
\`\`\`

### Skeleton (Loading)
\`\`\`tsx
import { Skeleton } from '@/components/ui/skeleton';

<div className="space-y-2">
  <Skeleton className="h-4 w-full" />
  <Skeleton className="h-4 w-3/4" />
  <Skeleton className="h-8 w-1/2" />
</div>
\`\`\`

### Command Palette
\`\`\`tsx
import { Command, CommandInput, CommandList, CommandEmpty, CommandGroup, CommandItem } from '@/components/ui/command';

<Command>
  <CommandInput placeholder="Search..." />
  <CommandList>
    <CommandEmpty>No results found.</CommandEmpty>
    <CommandGroup heading="Suggestions">
      <CommandItem>Item 1</CommandItem>
      <CommandItem>Item 2</CommandItem>
    </CommandGroup>
  </CommandList>
</Command>
\`\`\`

### Progress
\`\`\`tsx
import { Progress } from '@/components/ui/progress';

<Progress value={33} />
\`\`\`

### Alert
\`\`\`tsx
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';

<Alert>
  <AlertTitle>Heads up!</AlertTitle>
  <AlertDescription>
    You can add components to your app using the cli.
  </AlertDescription>
</Alert>
\`\`\`

---

## 🎨 Variants Reference

### Button Variants
- `default` - Primary blue button
- `secondary` - Gray button
- `destructive` - Red danger button
- `outline` - Outlined button
- `ghost` - Transparent button
- `link` - Text link button

### Badge Variants
- `default` - Primary badge
- `secondary` - Gray badge
- `destructive` - Red badge
- `outline` - Outlined badge

### Button Sizes
- `default` - Normal size
- `sm` - Small
- `lg` - Large
- `icon` - Icon-only (square)

---

## 🔗 Component Composition Patterns

### Form Pattern
\`\`\`tsx
<Card>
  <CardHeader>
    <CardTitle>Profile Settings</CardTitle>
  </CardHeader>
  <CardContent>
    <form className="space-y-4">
      <div>
        <Label htmlFor="name">Name</Label>
        <Input id="name" />
      </div>
      <div>
        <Label htmlFor="email">Email</Label>
        <Input id="email" type="email" />
      </div>
      <Button type="submit">Save Changes</Button>
    </form>
  </CardContent>
</Card>
\`\`\`

### Stats Card Pattern
\`\`\`tsx
<Card>
  <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
    <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
    <DollarSign className="h-4 w-4 text-muted-foreground" />
  </CardHeader>
  <CardContent>
    <div className="text-2xl font-bold">$45,231</div>
    <p className="text-xs text-muted-foreground">+20% from last month</p>
  </CardContent>
</Card>
\`\`\`

### Settings Pattern
\`\`\`tsx
<div className="flex items-center justify-between">
  <div className="space-y-0.5">
    <Label>Email Notifications</Label>
    <p className="text-sm text-muted-foreground">
      Receive emails about your account
    </p>
  </div>
  <Switch />
</div>
\`\`\`

---

## 📱 Responsive Classes

\`\`\`tsx
// Mobile first approach
<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
  // 1 column on mobile, 2 on tablet, 3 on desktop
</div>

<Button className="w-full md:w-auto">
  // Full width on mobile, auto on tablet+
</Button>
\`\`\`

---

**Toate componentele sunt fully accessible (ARIA), responsive, și themable!**
