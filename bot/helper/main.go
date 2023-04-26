package main

import (
	"fmt"
	"os"
	"unicode"

	swissknife "github.com/Sagleft/swiss-knife"
	"github.com/fatih/color"
	"gopkg.in/robfig/cron.v2"
)

const (
	appName     = "diano-bot"
	devAddress  = "F50AF5410B1F3F4297043F0E046F205BCBAA76BEC70E936EB0F3AB94BF316804"
	currencyTag = "CRP"
)

func main() {
	swissknife.PrintIntroMessage(appName, devAddress, currencyTag)

	cronSpec := os.Getenv("CRON_SPEC")
	if cronSpec == "" {
		color.Red("cron rules not set. exit")
		return
	}

	setupCron(cronSpec)

	swissknife.RunInBackground()
}

func parseCronSpec(spec string) string {
	runes := []rune(spec)
	if !unicode.IsDigit(runes[0]) {
		spec = "@" + spec
	}
	return spec
}

func setupCron(cronSpec string) {
	c := cron.New()
	c.AddFunc(parseCronSpec(cronSpec), func() {
		runBot()
	})
	c.Start()
}

func runBot() {
	fmt.Println("run bot..") // TEMP
}
