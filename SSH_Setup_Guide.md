# 🔑 Paano Mag-Setup ng SSH Key sa GitHub (Taglish Guide)

## 📋 Ano ang SSH Key?
Ang SSH key ay parang "digital na susi" na ginagamit para makapag-connect ka sa GitHub nang hindi na kailangan mag-enter ng password every time. Mas secure at convenient ito!

---

## 🚀 Step-by-Step Guide

### **Step 1: Check kung may existing SSH keys ka na**
```powershell
Get-ChildItem -Path $env:USERPROFILE\.ssh -Force
```

**Kung may makita kang files na:**
- `id_ed25519` (private key)
- `id_ed25519.pub` (public key)

**✅ Good! May SSH keys ka na! Skip to Step 3.**

**❌ Kung walang files:**
- Kailangan mo mag-generate ng bagong SSH key (Step 2)

---

### **Step 2: Generate ng bagong SSH key (kung walang existing)**
```powershell
ssh-keygen -t ed25519 -C "your_email@example.com"
```

**Replace `your_email@example.com` sa actual email mo sa GitHub**

**Mga tanong na lalabas:**
1. **"Enter a file in which to save the key"** → Press **Enter** (use default)
2. **"Enter passphrase"** → Type password mo (optional pero recommended)
3. **"Enter same passphrase again"** → Type ulit yung password

---

### **Step 3: Copy yung public key sa clipboard**
```powershell
Get-Content $env:USERPROFILE\.ssh\id_ed25519.pub | Set-Clipboard
```

**✅ Success! Naka-copy na sa clipboard mo yung public key**

---

### **Step 4: Add yung key sa GitHub account mo**

1. **Pumunta sa GitHub.com** at mag-login
2. **Click yung profile picture mo** (top right corner)
3. **Click "Settings"**
4. **Sa left sidebar, click "SSH and GPG keys"**
5. **Click yung green button na "New SSH key"**
6. **Fill out yung form:**
   - **Title:** "Windows Development Machine" o "My Laptop" (kahit ano)
   - **Key:** **Paste** (Ctrl+V) yung key na naka-copy sa clipboard mo
7. **Click "Add SSH key"**

---

### **Step 5: Test kung working na**
```powershell
ssh -T git@github.com
```

**Kung successful, makikita mo yung message:**
```
Hi [YourUsername]! You've successfully authenticated, but GitHub does not provide shell access.
```

**✅ Perfect! Working na yung SSH connection mo!**

---

## 🎯 Paano Gamitin

### **Para sa existing repository:**
```powershell
# Change yung remote URL to SSH
git remote set-url origin git@github.com:username/repository.git
```

### **Para sa bagong repository:**
```powershell
# Clone using SSH (hindi na kailangan ng password)
git clone git@github.com:username/repository.git
```

---

## 🔧 Troubleshooting

### **Problem: "Permission denied (publickey)"**
**Solution:**
1. Check kung tama yung key na na-add mo sa GitHub
2. Try ulit yung Step 3 (copy public key)
3. Make sure na yung correct email yung ginamit mo

### **Problem: "ssh-agent not running"**
**Solution:**
- Sa Windows, hindi kailangan ng ssh-agent
- Direktang gumamit ng SSH key, working na yan!

### **Problem: "No such file or directory"**
**Solution:**
- Make sure na may `.ssh` folder ka sa user directory
- Try mo mag-generate ng bagong key (Step 2)

---

## ✅ Benefits ng SSH Key

1. **No more password prompts** - Automatic authentication
2. **More secure** - Hindi mo na kailangan i-type yung password mo
3. **Faster operations** - Mas mabilis yung git push/pull
4. **Professional setup** - Standard practice sa development

---

## 🎉 Congratulations!

Kung nakita mo yung "successfully authenticated" message, **working na yung SSH setup mo!**

**Pwede mo na gamitin yung Git commands nang walang password prompts!**

---

## 📞 Need Help?

Kung may problema pa, check mo:
1. Yung exact error message
2. Kung tama yung email na ginamit mo
3. Kung naka-copy properly yung public key sa GitHub

**Happy coding! 🚀**
