import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Briefcase, MapPin, Clock, Users } from 'lucide-react';

export const metadata = {
  title: 'Careers at RentHub | Join Our Team',
  description: 'Explore career opportunities at RentHub. Join our team and help us revolutionize the property rental industry.',
};

export default function CareersPage() {
  const openings = [
    {
      title: 'Senior Full Stack Developer',
      department: 'Engineering',
      location: 'London, UK (Hybrid)',
      type: 'Full-time',
      description: 'Build and scale our platform using modern web technologies.',
    },
    {
      title: 'Product Designer',
      department: 'Design',
      location: 'Remote',
      type: 'Full-time',
      description: 'Create beautiful, intuitive experiences for our users.',
    },
    {
      title: 'Customer Success Manager',
      department: 'Customer Support',
      location: 'Berlin, Germany',
      type: 'Full-time',
      description: 'Help our customers get the most out of RentHub.',
    },
    {
      title: 'Marketing Manager',
      department: 'Marketing',
      location: 'Paris, France',
      type: 'Full-time',
      description: 'Drive growth and brand awareness across Europe.',
    },
    {
      title: 'Data Analyst',
      department: 'Data',
      location: 'Remote',
      type: 'Full-time',
      description: 'Turn data into insights that drive business decisions.',
    },
    {
      title: 'Property Operations Specialist',
      department: 'Operations',
      location: 'Madrid, Spain',
      type: 'Full-time',
      description: 'Ensure quality and compliance across our property listings.',
    },
  ];

  return (
  <TooltipProvider>
    <MainLayout>
      <div className="container mx-auto px-4 py-12">
        <div className="max-w-4xl mx-auto">
          {/* Hero */}
          <div className="text-center mb-12">
            <Badge className="mb-4">We&apos;re Hiring!</Badge>
            <h1 className="text-4xl md:text-5xl font-bold mb-4 animate-fade-in" style={{ animationDelay: '0ms' }}>
              Join Our Team
            </h1>
            <p className="text-xl text-muted-foreground mb-8 animate-fade-in" style={{ animationDelay: '100ms' }}>
              Help us build the future of property rentals
            </p>
          </div>

          {/* Why Join Us */}
          <div className="mb-16">
            <h2 className="text-3xl font-bold mb-8 text-center">Why RentHub?</h2>
            <div className="grid md:grid-cols-3 gap-6">
              <Card className="animate-fade-in-up" style={{ animationDelay: '0ms' }}>
                <CardHeader>
                  <Users className="h-10 w-10 text-primary mb-2" />
                  <CardTitle>Great Team</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-muted-foreground">
                    Work with talented people from around the world who are passionate about what they do.
                  </p>
                </CardContent>
              </Card>
              <Card className="animate-fade-in-up" style={{ animationDelay: '120ms' }}>
                <CardHeader>
                  <MapPin className="h-10 w-10 text-primary mb-2" />
                  <CardTitle>Flexible Work</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-muted-foreground">
                    Choose to work remotely, from one of our offices, or a hybrid approach that suits you.
                  </p>
                </CardContent>
              </Card>
              <Card className="animate-fade-in-up" style={{ animationDelay: '240ms' }}>
                <CardHeader>
                  <Briefcase className="h-10 w-10 text-primary mb-2" />
                  <CardTitle>Growth</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-muted-foreground">
                    Continuous learning opportunities, mentorship programs, and career development.
                  </p>
                </CardContent>
              </Card>
            </div>
          </div>

          {/* Benefits */}
          <div className="mb-16">
            <h2 className="text-3xl font-bold mb-6">Benefits & Perks</h2>
            <div className="grid md:grid-cols-2 gap-4">
              {[
                '💰 Competitive salary and equity',
                '🏥 Health, dental, and vision insurance',
                '🌴 Unlimited PTO policy',
                '💻 Latest tech and equipment',
                '📚 Learning and development budget',
                '🏠 Work from home stipend',
                '🍽️ Catered lunches and snacks',
                '🎉 Team events and offsites',
              ].map((benefit, index) => (
                <div key={index} className="flex items-center gap-3 p-4 border rounded-lg">
                  <span className="text-lg">{benefit}</span>
                </div>
              ))}
            </div>
          </div>

          {/* Open Positions */}
          <div className="mb-16">
            <h2 className="text-3xl font-bold mb-8">Open Positions</h2>
            <div className="space-y-4">
              {openings.map((job, index) => (
                <Card key={index} className="hover:border-primary transition-colors animate-fade-in-up" style={{ animationDelay: `${index * 60}ms` }}>
                  <CardHeader>
                    <div className="flex items-start justify-between">
                      <div>
                        <CardTitle className="mb-2">{job.title}</CardTitle>
                        <CardDescription>{job.description}</CardDescription>
                      </div>
                      <Badge variant="secondary">{job.department}</Badge>
                    </div>
                  </CardHeader>
                  <CardContent>
                    <div className="flex flex-wrap items-center gap-4 text-sm text-muted-foreground mb-4">
                      <div className="flex items-center gap-1">
                        <MapPin className="h-4 w-4" />
                        {job.location}
                      </div>
                      <div className="flex items-center gap-1">
                        <Clock className="h-4 w-4" />
                        {job.type}
                      </div>
                    </div>
                    <Tooltip>
                      <TooltipTrigger asChild>
                        <Button>Apply Now</Button>
                      </TooltipTrigger>
                      <TooltipContent>Submit your application</TooltipContent>
                    </Tooltip>
                  </CardContent>
                </Card>
              ))}
            </div>
          </div>

          {/* Don't see a fit? */}
          <Card className="bg-primary/5 border-primary/20 text-center">
            <CardHeader>
              <CardTitle>Don&apos;t see a perfect fit?</CardTitle>
              <CardDescription>
                We&apos;re always looking for talented people. Send us your resume and let us know what you&apos;re interested in.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <Tooltip>
                <TooltipTrigger asChild>
                  <Button size="lg">Send Your Resume</Button>
                </TooltipTrigger>
                <TooltipContent>Contact us with your resume</TooltipContent>
              </Tooltip>
            </CardContent>
          </Card>
        </div>
      </div>
    </MainLayout>
  </TooltipProvider>
  );
}
