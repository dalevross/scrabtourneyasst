import webapp2
import json
#import ijson.backends.python as ijson
import ijson
import msgpack
import errno
import sys

def getDefinitionMsgPack(dictionaryName,word,self):
        response = {}
        dictionary = {}
        
        try:
            infile = open(dictionaryName + ".mpack", "rb")
        except IOError as e:
            if e.errno == errno.ENOENT:
                response = {'found':False,'why':'Dictionary not supported'}
        else:
            self.response.write('No exception - about to unpack<br/>')
            with infile:
                try:
                    dictionary = msgpack.unpackb(infile.read())                               
                except Exception as e:
                    response = {"Error":e.message}
                else:
                     if len(dictionary) != 0:
                        if word == "":
                            response = {'found':False, 'why':'No word was submitted'}          
                        if word in dictionary:
                            definition = dictionary[word]
                            if(definition != ""):
                                response = {'found':True, 'definition':definition}
                            else:
                                response = {'found':False, 'why':'A definition for ' + word + ' was not found in ' + d.upper()}                        
                        else:
                                response = {'found':False, 'why':word + ' was not found in ' + d.upper()}
                     else:
                        response = {'found':False, 'why':'Dictionary ' + d + " is not supported"}
        return response
    
def getDefinition(dictionary,word):    
        response = {}
        try:
            infile = open(dictionary + ".json", "rb")
        except IOError as e:
            if e.errno == errno.ENOENT:
                response = {'found':False,'why':'Dictionary not supported'}
        else:
            with infile:
                try:
                    parser = ijson.parse(infile)
                    for prefix,event, value in parser:
                        if prefix == word:
                            response = {'found':True,'definition':value,'prefix':prefix}
                            return response                        
                    #File parsed successfully but nothing was found    
                    response = {'found':False,'definition':word + ' was not found in ' + dictionary.upper()}
                    return response
                except Exception as e:
                    response = {'found':False,'why':e}
                    
        return response
               
                    
class MainPage(webapp2.RequestHandler): 
    
    def get(self):
        response = {} 
        d = self.request.get('d', default_value='collins')
        word = self.request.get('word').upper()
        method = self.request.get('method')
        if method == "getdef":
            ''' 
            dictionary = getDictionary(d)
            if len(dictionary) != 0:
                if word == "":
                    response = {'found':False, 'why':'No word was submitted'}          
                if word in dictionary:
                    definition = dictionary[word]
                    if(definition != ""):
                        response = {'found':True, 'definition':dictionary[word],}
                    else:
                        response = {'found':False, 'why':'A definition for ' + word + ' was not found in ' + d.upper()}                        
                else:
                        response = {'found':False, 'why':word + ' was not found in ' + d.upper()}
            else:
                response = {'found':False, 'why':'Dictionary ' + d + " is not supported"}
               
                
            dictionary = {}
            '''
            response = getDefinitionMsgPack(d,word,self)
            #Write JSON Header
            self.response.headers['Content-Type'] = 'application/json'
            self.response.write(json.dumps(response))
           
            
application = webapp2.WSGIApplication([
    ('/', MainPage),
], debug=True)
