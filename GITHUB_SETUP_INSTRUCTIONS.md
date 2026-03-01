# GitHub Setup Instructions for MediStock

## ✅ What's Already Done

1. ✅ Git repository initialized in your project folder
2. ✅ All files added to Git
3. ✅ Initial commit created
4. ✅ README.md created
5. ✅ .gitignore configured properly

## 📤 Steps to Upload to GitHub

### Step 1: Create GitHub Repository

1. Go to [GitHub.com](https://github.com) and sign in
2. Click the **"+"** icon in the top right → **"New repository"**
3. Fill in the details:
   - **Repository name**: `medistock` (or any name you prefer)
   - **Description**: "Hospital Drug Inventory Management System with AI/ML Predictions"
   - **Visibility**: 
     - Choose **Public** (if you want to share publicly)
     - Choose **Private** (if you want to keep it private and share only with teammates)
   - **DO NOT** check "Initialize with README" (we already have one)
   - **DO NOT** add .gitignore or license (we already have them)
4. Click **"Create repository"**

### Step 2: Connect Local Repository to GitHub

After creating the repository, GitHub will show you commands. Use these:

```bash
# Add the remote repository (replace YOUR_USERNAME with your GitHub username)
git remote add origin https://github.com/YOUR_USERNAME/medistock.git

# Rename branch to main (if needed)
git branch -M main

# Push your code to GitHub
git push -u origin main
```

**Example:**
If your GitHub username is `johndoe`, the command would be:
```bash
git remote add origin https://github.com/johndoe/medistock.git
git branch -M main
git push -u origin main
```

### Step 3: Verify Upload

1. Go to your GitHub repository page
2. You should see all your files
3. README.md should display on the main page

## 👥 Sharing with Teammate

### Option 1: Add Collaborator (Recommended)

1. Go to your repository on GitHub
2. Click **"Settings"** tab
3. Click **"Collaborators"** in the left sidebar
4. Click **"Add people"**
5. Enter your teammate's GitHub username or email
6. Click **"Add [username] to this repository"**
7. Your teammate will receive an email invitation
8. Once they accept, they can clone and push to the repository

### Option 2: Make Repository Public

1. Go to **Settings** → **General**
2. Scroll down to **"Danger Zone"**
3. Click **"Change visibility"**
4. Select **"Make public"**
5. Anyone with the link can view it (but only collaborators can edit)

## 🔄 Working with Your Teammate

### For Your Teammate (First Time Setup)

```bash
# Clone the repository
git clone https://github.com/YOUR_USERNAME/medistock.git
cd medistock

# Install dependencies
composer install

# Set up database (same as installation steps)
docker compose up -d database
php bin/console doctrine:migrations:migrate
```

### Daily Workflow

**Before starting work:**
```bash
# Pull latest changes
git pull origin main
```

**After making changes:**
```bash
# Check what changed
git status

# Add your changes
git add .

# Commit with a message
git commit -m "Description of what you changed"

# Push to GitHub
git push origin main
```

**Example commit messages:**
- `git commit -m "Add new drug category filter"`
- `git commit -m "Fix stock update bug"`
- `git commit -m "Update dashboard statistics"`

## 📋 Important Files to Share

Make sure these are in the repository (they should be):
- ✅ `README.md` - Project overview
- ✅ `PROJECT_SUMMARY.md` - Complete documentation
- ✅ `composer.json` - Dependencies
- ✅ `.env` - Database configuration (with default values)
- ✅ All source code files

## ⚠️ Important Notes

### What NOT to Commit

The `.gitignore` file already excludes:
- `/vendor/` - Dependencies (teammate will run `composer install`)
- `/var/` - Cache and logs
- `/.env.local` - Local environment overrides
- Database credentials in `.env.local`

### Environment Variables

Your teammate should:
1. Copy `.env` to `.env.local` (if needed)
2. Update database credentials if different
3. Never commit `.env.local` (it's in .gitignore)

### Database Setup

Both you and your teammate need:
- Docker Desktop running
- Same database configuration (or update `.env.local`)

## 🐛 Troubleshooting

### "Repository not found" error
- Check the repository URL is correct
- Make sure you have access (if private repo)
- Verify your GitHub credentials

### "Permission denied" error
- Make sure you're added as a collaborator
- Check your Git credentials: `git config --global user.name` and `git config --global user.email`

### Merge conflicts
If you and your teammate edit the same file:
```bash
# Pull latest changes
git pull origin main

# Resolve conflicts in the files
# Then:
git add .
git commit -m "Resolve merge conflicts"
git push origin main
```

## 📞 Quick Reference

**Check repository status:**
```bash
git status
```

**See commit history:**
```bash
git log --oneline
```

**See what changed:**
```bash
git diff
```

**Undo local changes (be careful!):**
```bash
git checkout -- filename
```

---

**Your repository is ready!** Just create the GitHub repo and push your code. 🚀










