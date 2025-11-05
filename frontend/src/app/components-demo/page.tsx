"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Checkbox } from "@/components/ui/checkbox"
import { Switch } from "@/components/ui/switch"
import { Slider } from "@/components/ui/slider"
import { Progress } from "@/components/ui/progress"
import { Badge } from "@/components/ui/badge"
import { Alert } from "@/components/ui/alert"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Separator } from "@/components/ui/separator"
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from "@/components/ui/breadcrumb"
import { Calendar } from "@/components/ui/calendar"
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Textarea } from "@/components/ui/textarea"
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/ui/accordion"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"

export default function ComponentsDemo() {
  const [date, setDate] = useState<Date | undefined>(new Date())
  const [progress, setProgress] = useState(45)

  return (
    <div className="container mx-auto py-10 space-y-8">
      <div>
        <h1 className="text-4xl font-bold mb-2">shadcn/ui Components Demo</h1>
        <p className="text-muted-foreground mb-4">
          All shadcn/ui components have been successfully added to the project
        </p>
        
        <Breadcrumb>
          <BreadcrumbList>
            <BreadcrumbItem>
              <BreadcrumbLink href="/">Home</BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbPage>Components Demo</BreadcrumbPage>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>
      </div>

      <Separator />

      <Tabs defaultValue="forms" className="w-full">
        <TabsList className="grid w-full grid-cols-4">
          <TabsTrigger value="forms">Forms</TabsTrigger>
          <TabsTrigger value="feedback">Feedback</TabsTrigger>
          <TabsTrigger value="data">Data Display</TabsTrigger>
          <TabsTrigger value="layout">Layout</TabsTrigger>
        </TabsList>

        <TabsContent value="forms" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Form Components</CardTitle>
              <CardDescription>Input fields, checkboxes, selects, and more</CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              <div className="space-y-2">
                <Label htmlFor="email">Email</Label>
                <Input id="email" type="email" placeholder="Enter your email" />
              </div>

              <div className="space-y-2">
                <Label htmlFor="message">Message</Label>
                <Textarea id="message" placeholder="Type your message here" />
              </div>

              <div className="space-y-2">
                <Label>Property Type</Label>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="Select property type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="apartment">Apartment</SelectItem>
                    <SelectItem value="house">House</SelectItem>
                    <SelectItem value="condo">Condo</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div className="flex items-center space-x-2">
                <Checkbox id="terms" />
                <Label htmlFor="terms">Accept terms and conditions</Label>
              </div>

              <div className="space-y-2">
                <Label>Price Range</Label>
                <Slider defaultValue={[50]} max={100} step={1} />
              </div>

              <div className="flex items-center space-x-2">
                <Switch id="notifications" />
                <Label htmlFor="notifications">Enable notifications</Label>
              </div>

              <div className="space-y-2">
                <Label>Rental Type</Label>
                <RadioGroup defaultValue="monthly">
                  <div className="flex items-center space-x-2">
                    <RadioGroupItem value="monthly" id="monthly" />
                    <Label htmlFor="monthly">Monthly</Label>
                  </div>
                  <div className="flex items-center space-x-2">
                    <RadioGroupItem value="yearly" id="yearly" />
                    <Label htmlFor="yearly">Yearly</Label>
                  </div>
                </RadioGroup>
              </div>

              <div className="flex gap-2">
                <Button>Submit</Button>
                <Button variant="outline">Cancel</Button>
                <Button variant="destructive">Delete</Button>
                <Button variant="secondary">Secondary</Button>
                <Button variant="ghost">Ghost</Button>
                <Button variant="link">Link</Button>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="feedback" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Feedback Components</CardTitle>
              <CardDescription>Alerts, badges, progress indicators</CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              <Alert>
                <p className="font-medium">Default Alert</p>
                <p className="text-sm">This is a default alert message.</p>
              </Alert>

              <div className="flex gap-2 flex-wrap">
                <Badge>Default</Badge>
                <Badge variant="secondary">Secondary</Badge>
                <Badge variant="destructive">Destructive</Badge>
                <Badge variant="outline">Outline</Badge>
              </div>

              <div className="space-y-2">
                <div className="flex justify-between text-sm">
                  <span>Progress</span>
                  <span>{progress}%</span>
                </div>
                <Progress value={progress} />
                <div className="flex gap-2">
                  <Button size="sm" onClick={() => setProgress(Math.max(0, progress - 10))}>
                    Decrease
                  </Button>
                  <Button size="sm" onClick={() => setProgress(Math.min(100, progress + 10))}>
                    Increase
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="data" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Data Display Components</CardTitle>
              <CardDescription>Tables, calendars, and more</CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              <div>
                <h3 className="text-lg font-semibold mb-2">Calendar</h3>
                <Calendar
                  mode="single"
                  selected={date}
                  onSelect={setDate}
                  className="rounded-md border"
                />
              </div>

              <Separator />

              <div>
                <h3 className="text-lg font-semibold mb-2">Table</h3>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Property</TableHead>
                      <TableHead>Location</TableHead>
                      <TableHead>Price</TableHead>
                      <TableHead>Status</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow>
                      <TableCell>Modern Apartment</TableCell>
                      <TableCell>New York</TableCell>
                      <TableCell>$2,500/mo</TableCell>
                      <TableCell><Badge>Available</Badge></TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell>Beach House</TableCell>
                      <TableCell>Miami</TableCell>
                      <TableCell>$3,200/mo</TableCell>
                      <TableCell><Badge variant="secondary">Rented</Badge></TableCell>
                    </TableRow>
                    <TableRow>
                      <TableCell>Downtown Condo</TableCell>
                      <TableCell>Chicago</TableCell>
                      <TableCell>$1,800/mo</TableCell>
                      <TableCell><Badge>Available</Badge></TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="layout" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Layout Components</CardTitle>
              <CardDescription>Accordions, cards, and separators</CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              <Accordion type="single" collapsible className="w-full">
                <AccordionItem value="item-1">
                  <AccordionTrigger>What is RentHub?</AccordionTrigger>
                  <AccordionContent>
                    RentHub is a comprehensive property rental management platform that connects property owners with potential tenants.
                  </AccordionContent>
                </AccordionItem>
                <AccordionItem value="item-2">
                  <AccordionTrigger>How do I list a property?</AccordionTrigger>
                  <AccordionContent>
                    You can list a property by navigating to the Owner Dashboard and clicking on "Add New Property".
                  </AccordionContent>
                </AccordionItem>
                <AccordionItem value="item-3">
                  <AccordionTrigger>What are the fees?</AccordionTrigger>
                  <AccordionContent>
                    We charge a small commission only when a booking is confirmed. Check our pricing page for details.
                  </AccordionContent>
                </AccordionItem>
              </Accordion>

              <Separator />

              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card>
                  <CardHeader>
                    <CardTitle>Card 1</CardTitle>
                    <CardDescription>Simple card example</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <p>This is a card component with header and content.</p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader>
                    <CardTitle>Card 2</CardTitle>
                    <CardDescription>Another card</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <p>Cards can be arranged in grids.</p>
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader>
                    <CardTitle>Card 3</CardTitle>
                    <CardDescription>Third card</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <p>Responsive grid layout example.</p>
                  </CardContent>
                </Card>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>

      <Card>
        <CardHeader>
          <CardTitle>All Components Added ✓</CardTitle>
          <CardDescription>
            57 total components including: alert-dialog, aspect-ratio, breadcrumb, button-group, 
            calendar, carousel, chart, checkbox, collapsible, command, context-menu, drawer, 
            field, form, hover-card, input-group, input-otp, menubar, pagination, popover, 
            progress, radio-group, resizable, scroll-area, slider, sonner, spinner, switch, 
            table, textarea, toggle, toggle-group, tooltip, and more.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-muted-foreground">
            For detailed documentation and usage examples, see the SHADCN_COMPONENTS.md file 
            in the frontend directory.
          </p>
        </CardContent>
      </Card>
    </div>
  )
}
