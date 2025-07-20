**car_images**
```sql
- id (Primary Key)
- car_id (Foreign Key → cars.id)
- image_path (VARCHAR)
- is_primary (BOOLEAN, default: false)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

**customer_favorites**
```sql
- id (Primary Key)
- customer_id (Foreign Key → users.id)
- car_id (Foreign Key → cars.id)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```### 5.7 User Experience Features
- **FR-49**: Wishlist/favorites functionality for cars
- **FR-50**: Detailed booking history with expandable views
- **FR-51**: Advanced search filters (price range, location, car features, fuel type, transmission)
- **FR-52**: Profile picture uploads for users and agencies
- **FR-53**: Car image galleries for listings
- **FR-54**: Renter dashboard with comprehensive statistics:
  - Total earnings
  - Number of bookings (today, week, month)
  - Most popular cars
  - Average rating
  - Booking trends and analytics**reviews**
```sql
- id (Primary Key)
- reservation_id (Foreign Key → reservations.id, UNIQUE)
- customer_id (Foreign Key → users.id)
- agency_id (Foreign Key → agencies.id)
- rating (INTEGER, 1-5)
- comment (TEXT, nullable)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```**car_unavailability**
```sql
- id (Primary Key)
- car_id (Foreign Key → cars.id)
- start_date (DATE)
- end_date (DATE)
- reason (ENUM: 'reservation', 'maintenance', 'renter_blocked')
- created_by (Foreign Key → users.id)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```### 5.3 Car Management
- **FR-15**: Public browsing of available cars (no login required)
- **FR-16**: Display car details (make, model, year, price, agency, category, specifications)
- **FR-17**: Cars categorized by type (economy, luxury, SUV, sedan, etc.)
- **FR-18**: Featured cars system for highlighting popular/premium vehicles
- **FR-19**: Car image uploads for listings (multiple images per car)
- **FR-20**: Detailed car specifications (fuel type, transmission, seats, features)
- **FR-21**: Verified renters can add new cars to their inventory with category selection
- **FR-22**: Renters can edit/update their own car information and mark as featured
- **FR-23**: Renters can mark cars as temporarily unavailable for specific dates
- **FR-24**: Admins can delist cars with reason (but cannot list new cars)
- **FR-25**: Admins cannot add new cars to the system# Product Requirements Document (PRD) - Car Rental System
## School Project - Simplified & Well-Structured

## 1. Introduction

This Product Requirements Document (PRD) outlines the requirements for a car rental system designed as a school project to demonstrate full-stack web development proficiency. The system will be built using Laravel and Vite with a focus on clean architecture, proper database design, and intuitive user interfaces. The project supports three distinct user roles with a streamlined feature set that can be completed within a one-week timeline while maintaining high code quality and structure.

## 2. Project Goals

**Primary Goal**: Demonstrate comprehensive full-stack web development skills through a well-architected car rental application.

**Key Objectives**:
- Showcase proficiency in Laravel framework and modern web development practices
- Implement clean, maintainable code following Laravel conventions
- Design and implement a proper relational database structure
- Create intuitive user interfaces for multiple user roles
- Demonstrate understanding of authentication, authorization, and role-based access control
- Complete a functional MVP within one week that impresses academically

## 3. Project Scope

### 3.1 In Scope (MVP Features)
- Three-role user system (Customer, Renter, Admin)
- Basic authentication and authorization
- Car listing and browsing functionality
- Simple reservation system with availability checking
- Renter verification workflow
- Basic administrative management
- Clean, responsive user interface

### 3.2 Out of Scope (Future Enhancements)
- Payment processing integration
- Advanced search and filtering
- File upload functionality
- Email notifications
- Complex reporting and analytics
- Mobile app development
- Third-party integrations (initially)

## 4. User Roles and Responsibilities

### 4.1 Customer (End User)
- Browse available cars and agencies
- Register and login to the system
- Make car reservations
- View personal booking history

### 4.2 Renter (Car Owner/Agency)
- Register as a renter
- Submit agency information for verification
- Manage car inventory (after verification)
- View bookings for their cars

### 4.3 Administrator
- Verify and approve renter applications
- Manage all users and renters
- Oversee car inventory
- Monitor system reservations

## 5. Functional Requirements

### 5.1 Authentication & User Management
- **FR-1**: Users can register with email and password (single-step for customers)
- **FR-2**: Renters have a two-step registration process:
  - Step 1: Basic user information (name, email, password, role selection)
  - Step 2: Agency information form (name, contact details, address, description)
- **FR-3**: System supports role-based access (Customer, Renter, Admin)
- **FR-4**: Users can login with email and password
- **FR-5**: Users can logout securely
- **FR-6**: Basic profile management for all user types with profile picture upload
- **FR-7**: Email notifications for admin when new agency registration occurs
- **FR-8**: Admin can approve/deny agency applications with reason
- **FR-9**: Email notifications for users and renters on key actions

### 5.2 Landing Page & Search System
- **FR-6**: Landing page with date-based availability filter
- **FR-7**: Landing page displays cars organized by categories (economy, luxury, SUV, etc.)
- **FR-8**: Landing page shows featured cars section
- **FR-9**: Landing page displays agencies ranked by their rating
- **FR-10**: Advanced search functionality redirects to dedicated search results page
- **FR-11**: Display "no results" message with alternative car suggestions when no matches found
- **FR-12**: Agency search functionality with dedicated results page
- **FR-13**: Individual agency profile pages showing all cars (available and rented status)
- **FR-14**: Real-time availability checking based on selected date ranges

### 5.4 Agency Management & Verification
- **FR-24**: Two-step renter registration process:
  - Step 1: Basic account creation (handled in authentication)
  - Step 2: Agency information submission
- **FR-25**: After agency submission, renter gains basic access with pending status
- **FR-26**: System sends email notification to admin for new agency applications
- **FR-27**: Renters can view their verification status with admin feedback
- **FR-28**: Admins can approve/reject applications with detailed reason
- **FR-29**: Approved renters gain full access to car management features
- **FR-30**: Public agency profiles showing all cars with availability status
- **FR-31**: Agency rating system based on customer reviews and bookings
- **FR-32**: Agencies displayed on landing page ranked by rating

### 5.5 Reservation System & Payments
- **FR-33**: Customers can search for cars available on specific dates
- **FR-34**: Advanced availability logic: cars unavailable only for specifically reserved dates
- **FR-35**: Customers can make reservations for available cars on selected dates
- **FR-36**: Mock payment system for demonstration purposes (no real transactions)
- **FR-37**: System prevents double-booking through real-time availability checking
- **FR-38**: Email confirmation sent to customers upon successful booking
- **FR-39**: Email notification sent to renters when their car is booked
- **FR-40**: Customers can view their reservation history with details
- **FR-41**: Renters can view all bookings for their cars
- **FR-42**: Renters can see customer information for their car reservations
- **FR-43**: Basic reservation status management (active, completed, cancelled)
- **FR-44**: Customer review system for completed rentals (affects agency rating)

### 5.6 Administrative Functions
- **FR-42**: Admin dashboard with comprehensive statistics:
  - Total number of users
  - Total revenue calculations
  - Today's rental statistics
  - This week's rental statistics
  - This month's rental statistics
- **FR-43**: User management with ban/unban functionality
- **FR-44**: Car management: can delist cars with reason (cannot add new cars)
- **FR-45**: Cannot add new users to the system
- **FR-46**: System-wide reservation monitoring
- **FR-47**: Renter verification and approval management
- **FR-48**: Agency rating oversight and management

## 6. User Stories

### 6.1 Customer Stories
- **As a visitor**, I want to see a landing page with categorized cars so I can browse by vehicle type
- **As a visitor**, I want to see featured cars so I can discover popular options
- **As a visitor**, I want to see top-rated agencies so I can choose trusted rental companies
- **As a visitor**, I want to use a date filter so I can quickly find available cars
- **As a customer**, I want to search for cars with advanced filters so I can find exactly what I need
- **As a customer**, I want to add cars to my wishlist so I can save favorites for later
- **As a customer**, I want to see detailed car specifications and images so I can make informed decisions
- **As a customer**, I want to register an account with profile picture so I can personalize my experience
- **As a customer**, I want to book a car with mock payment so I can complete the rental process
- **As a customer**, I want to receive email confirmations so I know my booking is confirmed
- **As a customer**, I want to view detailed booking history so I can track all my rentals
- **As a customer**, I want to leave reviews for completed rentals so I can help other customers

### 6.2 Renter Stories
- **As a new renter**, I want to register my basic account information so I can start the agency process
- **As a renter**, I want to complete my agency information with profile picture so I can apply for verification
- **As a pending renter**, I want to see my verification status so I know when I can start listing cars
- **As a verified renter**, I want to add cars with detailed specs and images so customers can see full details
- **As a renter**, I want to see comprehensive dashboard statistics so I can track my business performance
- **As a renter**, I want to view my earnings and booking trends so I can make informed business decisions
- **As a renter**, I want to receive email notifications when my cars are booked so I stay informed
- **As a renter**, I want to see which cars are most popular so I can optimize my inventory
- **As a renter**, I want to update my car information and set unavailable dates so I can manage my fleet
- **As a renter**, I want to view all bookings with customer details so I can manage my business
- **As a renter**, I want customers to be able to rent my cars through the platform so I can earn revenue

### 6.3 Admin Stories
- **As an admin**, I want to see a comprehensive dashboard with statistics so I can monitor the business
- **As an admin**, I want to view total users and revenue metrics so I can track growth
- **As an admin**, I want to see rental statistics for today, this week, and this month so I can analyze trends
- **As an admin**, I want to receive email notifications when new agencies register so I can review them promptly
- **As an admin**, I want to approve/reject applications with specific reasons so renters understand decisions
- **As an admin**, I want to ban/unban users so I can maintain platform quality
- **As an admin**, I want to delist cars with reasons so I can remove inappropriate listings
- **As an admin**, I want to monitor all reservations so I can oversee system activity
- **As an admin**, I should not be able to add cars or users so the platform maintains proper roles

## 7. Technical Requirements

### 7.1 Technology Stack
- **Backend**: Laravel 10+ (latest stable)
- **Frontend**: Vite with vanilla JavaScript or lightweight framework
- **Database**: PostgreSQL
- **Authentication**: Laravel Breeze
- **Styling**: Tailwind CSS or Bootstrap
- **Server**: Development server (Artisan serve)

### 7.2 Architecture Principles
- Follow Laravel MVC pattern strictly
- Use Eloquent ORM for database interactions
- Implement proper request validation
- Use Laravel's built-in authentication
- Maintain clean separation of concerns

## 8. Database Schema

### 8.1 Core Tables

**users**
```sql
- id (Primary Key)
- name (VARCHAR)
- email (VARCHAR, UNIQUE)
- password (VARCHAR)
- user_type (ENUM: 'customer', 'renter', 'admin')
- profile_picture (VARCHAR, nullable)
- is_banned (BOOLEAN, default: false)
- banned_reason (TEXT, nullable)
- banned_by (Foreign Key → users.id, nullable)
- banned_at (TIMESTAMP, nullable)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

**agencies**
```sql
- id (Primary Key)
- renter_id (Foreign Key → users.id, UNIQUE)
- name (VARCHAR)
- contact_email (VARCHAR)
- contact_phone (VARCHAR)
- address (TEXT)
- description (TEXT)
- rating (DECIMAL, default: 0.00)
- total_reviews (INTEGER, default: 0)
- verification_status (ENUM: 'pending', 'approved', 'rejected')
- admin_feedback (TEXT, nullable)
- submitted_at (TIMESTAMP)
- reviewed_at (TIMESTAMP, nullable)
- reviewed_by (Foreign Key → users.id, nullable)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

**cars**
```sql
- id (Primary Key)
- agency_id (Foreign Key → agencies.id)
- make (VARCHAR)
- model (VARCHAR)
- year (INTEGER)
- category (ENUM: 'economy', 'luxury', 'suv', 'sedan', 'hatchback', 'convertible')
- license_plate (VARCHAR, UNIQUE)
- rental_price_per_day (DECIMAL)
- fuel_type (ENUM: 'gasoline', 'diesel', 'electric', 'hybrid')
- transmission (ENUM: 'manual', 'automatic')
- seats (INTEGER)
- features (JSON)
- is_featured (BOOLEAN, default: false)
- is_active (BOOLEAN, default: true)
- delisted_reason (TEXT, nullable)
- delisted_by (Foreign Key → users.id, nullable)
- delisted_at (TIMESTAMP, nullable)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

**reservations**
```sql
- id (Primary Key)
- customer_id (Foreign Key → users.id)
- car_id (Foreign Key → cars.id)
- start_date (DATE)
- end_date (DATE)
- total_price (DECIMAL)
- status (ENUM: 'active', 'completed', 'cancelled')
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### 8.2 Relationships
- User → Agency (One-to-One for renters)
- Agency → Cars (One-to-Many)
- User → Reservations (One-to-Many for customers)
- Car → Reservations (One-to-Many)
- Car → Car_Unavailability (One-to-Many)
- User → Car_Unavailability (One-to-Many, created_by)
- User → Users (One-to-Many for ban relationships)
- User → Cars (One-to-Many for delist relationships)

## 9. User Interface Requirements

### 9.1 Design Principles
- Clean, professional appearance with modern aesthetics
- Responsive design for desktop and mobile
- **Simple navigation bar with logo, login, and signup buttons only**
- **Minimalist footer with copyright and social media links only**
- Intuitive user experience across all user roles
- Consistent styling across all pages
- Clear form validation and error messages
- Category-based organization for easy browsing

### 9.2 Key Pages
- **Public**: 
  - **Enhanced Landing page with:**
    - Date filter search widget
    - Cars by categories (economy, luxury, SUV, etc.)
    - Featured cars section
    - Top-rated agencies section
  - Advanced search results page with filters and suggestions
  - Cars browse page (categorized with detailed specs)
  - Agencies browse page (rating-sorted)
  - Individual agency profile pages with image galleries
  - Car details pages with full specifications and image carousel
- **Auth**: Login, Register (with two-step for renters)
- **Customer**: 
  - Dashboard with favorites and recent searches
  - Advanced search with filters
  - Wishlist/Favorites management
  - Detailed reservation history
  - Profile management with picture upload
  - Review system
- **Renter**: 
  - **Comprehensive Dashboard with statistics:**
    - Total earnings and revenue trends
    - Booking analytics (today, week, month)
    - Most popular cars
    - Average agency rating
    - Performance metrics
  - My Cars management (with specs, images, featured options)
  - Bookings with customer info and notifications
  - Agency profile management with image upload
- **Admin**: 
  - Statistics dashboard with system-wide metrics
  - Users management (with ban functionality)
  - Cars management (delist only)
  - Reservations monitoring
  - Renter verification
  - Reviews and ratings oversight

### 9.3 Navigation & Layout
- **Header**: Simple navbar with logo (left), Login/Signup buttons (right)
- **Footer**: Copyright notice and social media icons only
- **Content**: Focus on functionality and user experience
- **Mobile-first**: Responsive design for all screen sizes

## 10. Development Phases

The development process is organized into logical phases that can be completed at your own pace:

### Phase 1: Foundation Setup
- Laravel project initialization and configuration
- Database setup with PostgreSQL
- Laravel Breeze authentication system
- Basic routing and middleware structure
- Database migrations for all tables
- Model relationships and seed data

### Phase 2: Enhanced Authentication & Verification
- Two-step registration flow for renters
- Role-based dashboard and navigation
- Email notification system for admin alerts
- Agency verification interface with approval/rejection workflow
- Status tracking and feedback system

### Phase 3: Core Business Logic
- Car management system (CRUD operations)
- Public car and agency browsing
- Renter car inventory management
- Admin oversight of all cars and agencies

### Phase 4: Reservation System
- Booking logic with availability checking
- Customer reservation interface
- Reservation management for all user types
- Status tracking and basic reporting

### Phase 5: Polish & Enhancement
- UI/UX improvements and responsive design
- Form validation and error handling
- Testing and bug fixes
- Documentation and code cleanup

## 11. Quality Assurance

### 11.1 Code Quality Standards
- Follow PSR-12 coding standards
- Use descriptive variable and function names
- Implement proper error handling
- Write clean, commented code
- Use Laravel best practices

### 11.2 Testing Strategy
- Manual testing of all user flows
- Database relationship verification
- Form validation testing
- Role-based access testing
- Cross-browser compatibility check

## 12. Success Criteria

### 12.1 Technical Success
- All core features implemented and functional
- Clean, well-structured codebase
- Proper database design with relationships
- Responsive and intuitive user interface
- No critical bugs or security issues

### 12.2 Academic Success
- Demonstrates full-stack development skills
- Shows understanding of MVC architecture
- Exhibits proper project planning and execution
- Includes comprehensive documentation
- Presents a professional, polished product

## 13. Future Enhancements (Post-MVP)

### 13.1 Phase 2 Features
- Google OAuth integration
- Email notification system
- Advanced search and filtering
- File upload for business documents
- Payment processing integration

### 13.2 Phase 3 Features
- Mobile-responsive improvements
- Advanced reporting and analytics
- Multi-language support
- API development
- Real-time notifications

## 14. Conclusion

This car rental system project is designed to showcase comprehensive full-stack development skills while maintaining a realistic scope for a one-week academic project. The focus on clean architecture, proper database design, and intuitive user interfaces will demonstrate both technical proficiency and professional development practices.

The simplified feature set ensures successful completion within the timeline while providing a solid foundation for future enhancements. The three-role system adds sufficient complexity to showcase advanced concepts without overwhelming the development process.

This project will serve as an excellent demonstration of Laravel framework knowledge, database design skills, and full-stack web development capabilities for academic evaluation.